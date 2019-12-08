<?php

namespace Dynamic\Foxy\Inventory\Extension;

use Dynamic\Foxy\Inventory\Model\CartReservation;
use Dynamic\Foxy\Orders\Model\OrderDetail;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\ReadonlyField;
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
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName([
            'PurchaseLimit',
            'EmbargoLimit',
            'NumberPurchased',
        ]);

        $fields->addFieldsToTab('Root.Ecommerce.Inventory', array(
            Wrapper::create(
                CheckboxField::create('ControlInventory', 'Control Inventory?')
                    ->setDescription('limit the number of this product available for purchase'),
                Wrapper::create(
                    NumericField::create('PurchaseLimit')
                        ->setTitle('Number Available')
                        ->setDescription('add to cart form will be disabled once number available equals purchased'),
                    ReadonlyField::create('NumberAvailable', 'Remaining Available', $this->getNumberAvailable())
                        ->setDescription('This takes into account products added to the cart. Products removed from the cart may persist in the "Cart Reservations" until the expiration time.')//,
                )->displayIf('ControlInventory')->isChecked()->end()
            )->displayIf('Available')->isChecked()->end()
        ));
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
        return (int)$this->owner->PurchaseLimit - (int)$this->getNumberPurchased() - (int)$this->getCartReservations()->count();
    }

    /**
     * @return int
     */
    public function getNumberPurchased()
    {
        return OrderDetail::get()->filter('ProductID', $this->owner->ID)->sum('Quantity');
    }

    /**
     * @return DataList|bool
     */
    public function getOrders()
    {
        if ($this->owner->ID) {
            return OrderDetail::get()->filter('ProductID', $this->owner->ID);
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
