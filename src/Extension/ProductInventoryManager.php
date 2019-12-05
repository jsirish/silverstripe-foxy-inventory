<?php

namespace Dynamic\Foxy\Inventory\Extension;

use Dynamic\Foxy\Inventory\Model\CartReservation;
use Dynamic\Foxy\Orders\Model\OrderDetail;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataExtension;
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
     * @var
     */
    private $number_available;

    /**
     * @var
     */
    private $number_purchased;

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
                    ReadonlyField::create('NumberPurchased', 'Purchased', $this->getNumberPurchased())//,
                )->displayIf('ControlInventory')->isChecked()->end()
            )->displayIf('Available')->isChecked()->end()
        ));

        if ($this->getCartReservations()) {
            $expirationGrid = GridField::create(
                'CartReservations',
                'Cart Reservations',
                $this->getCartReservations()->sort('Created'),
                $cartResConfig = GridFieldConfig_RecordViewer::create()
            );
            $expirationGrid->displayIf('ControlInventory')->isChecked()->end();

            $fields->addFieldToTab('Root.Inventory', $expirationGrid);
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
            return $this->owner->getNumberPurchased() <= $this->owner->PurchaseLimit;
        } else if ($this->owner->isAvailable()) {
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getNumberAvailable()
    {
        if (!$this->number_available) {
            $this->setNumberAvailable();
        }

        return $this->number_available;
    }

    /**
     * @return $this
     */
    public function setNumberAvailable()
    {
        if ($this->getIsProductAvailable()) {
            $this->number_available =  (int)$this->owner->PurchaseLimit - (int)$this->getNumberPurchased();
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getNumberPurchased()
    {
        if (!$this->number_purchased) {
            $this->setNumberPurchased();
        }

        return $this->number_purchased;
    }

    /**
     * @return $this
     */
    public function setNumberPurchased()
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
        if ($this->getCartReservations()) {
            foreach ($this->getCartReservations() as $reservation) {
                $ct += 1;
            }
        }

        $this->number_purchased = $ct;

        return $this;
    }

    /**
     * @return DataList
     */
    public function getOrders()
    {
        if ($this->owner->ID) {
            return OrderDetail::get()->filter('ProductID', $this->owner->ID);
        }
        return false;
    }

    /**
     * @return int
     */
    public function getCartReservations()
    {
        $reservations = CartReservation::get()->filter('ProductID', $this->owner->ID)
            ->filter('Expires:GreaterThan', date('Y-m-d H:i:s', strtotime('now')));

        return $reservations;
    }
}
