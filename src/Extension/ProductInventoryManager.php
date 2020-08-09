<?php

namespace Dynamic\Foxy\Inventory\Extension;

use Dynamic\Foxy\Inventory\Model\CartReservation;
use Dynamic\Foxy\Orders\Model\OrderDetail;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ValidationResult;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class ProductInventoryManager extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'ControlInventory' => 'Boolean',
        'PurchaseLimit' => 'Int',
        'NumberPurchased' => 'Int',
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'ControlInventory',
            'PurchaseLimit',
            'EmbargoLimit',
            'NumberPurchased',
        ]);

        $fields->addFieldsToTab('Root.Inventory', [
            Wrapper::create(
                CheckboxField::create('ControlInventory', 'Control Inventory?')
                    ->setDescription('limit the number of this product available for purchase'),
                Wrapper::create(
                    NumericField::create('PurchaseLimit')
                        ->setTitle('Number Available')
                        ->setDescription('add to cart form will be disabled once number available equals purchased'),
                    ReadonlyField::create('NumberPurchased', 'Number Purchased', $this->owner->NumberPurchased)
                        ->setDescription('Number of products purchased all time'),
                    ReadonlyField::create('NumberAvailable', 'Remaining Available', $this->getNumberAvailable())
                        ->setDescription('This takes into account products added to the cart. Products removed from the cart may persist in the "Cart Reservations" until the expiration time.')//phpcs:ignore
                )->displayIf('ControlInventory')->isChecked()->end()
            )->displayIf('Available')->isChecked()->end(),
        ]);
    }

    /**
     * @param ValidationResult $validationResult
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function validate(ValidationResult $validationResult)
    {
        parent::validate($validationResult);

        if ($this->owner->ControlInventory && $this->owner->PurchaseLimit == 0) {
            $validationResult->addFieldError('PurchaseLimit', 'You must specify a purchase limit more than 0');
        }
    }

    /**
     * @param $available
     */
    public function updateGetIsAvailable(&$available)
    {
        if ($this->owner->Variations()->count()) {
            $available = false;
            foreach ($this->owner->Variations() as $variation) {
                if ($variation->getIsAvailable()) {
                    $available = true;
                }
            }
        } elseif ($this->getHasInventory()) {
            $available = $this->getIsProductAvailable();
        }
    }

    /**
     * @return bool
     */
    public function getHasInventory()
    {
        return $this->owner->ControlInventory && $this->owner->PurchaseLimit != 0;
    }

    /**
     * @return bool
     */
    public function getIsProductAvailable()
    {
        if ($this->owner->getHasInventory()) {
            return $this->getNumberAvailable() > 0;
        }

        return true;
    }

    /**
     * @return int|void
     */
    public function getNumberAvailable()
    {
        return (int)$this->owner->PurchaseLimit - (int)$this->owner->NumberPurchased - (int)$this->getCartReservations()->count();
    }

    /**
     * @return int
     */
    public function getNumberPurchasedUpdate()
    {
        $ct = 0;
        if ($this->getOrders()) {
            foreach ($this->getOrders() as $order) {
                $ct += $order->Quantity;
            }
        }
        return $ct;
    }

    /**
     * @return DataList|bool
     */
    public function getOrders()
    {
        if ($this->owner->ID) {
            $orderDetails = OrderDetail::get()->filter('ProductID', $this->owner->ID);
            $orders = ArrayList::create();
            foreach ($orderDetails as $orderDetail) {
                $hasVariation = false;
                foreach ($orderDetail->OrderVariations() as $variation) {
                    if ($variation->VariationID > 0) {
                        $hasVariation = true;
                    }
                }
                if (!$hasVariation) {
                    $orders->push($orderDetail);
                }
            }

            return $orders;
        }

        return false;
    }

    /**
     * @return DataList
     */
    public function getCartReservations()
    {
        return CartReservation::get()
            ->filter([
                'ProductID' => $this->owner->ID,
                'Expires:GreaterThan' => date('Y-m-d H:i:s', strtotime('now')),
            ]);
    }
}
