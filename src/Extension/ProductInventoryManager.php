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

        $fields->addFieldsToTab('Root.Inventory', array(
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
            return $this->owner->PurchaseLimit > $this->getNumberAvailable();
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
        $ct = 0;
        if ($this->getOrders()) {
            /** @var OrderDetail $order */
            foreach ($this->getOrders() as $order) {
                if ($order->OrderID !== 0) {
                    $ct += $order->Quantity;
                }
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
            return OrderDetail::get()->filter('ProductID', $this->owner->ID);
        }

        return false;
    }

    /**
     * @return DataList
     */
    public function getCartReservations()
    {
        $reservations = CartReservation::get()->filter('ProductID', $this->owner->ID)
            ->filter('Expires:GreaterThan', date('Y-m-d H:i:s', strtotime('now')));

        return $reservations;
    }
}
