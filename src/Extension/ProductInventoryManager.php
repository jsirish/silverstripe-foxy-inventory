<?php

namespace Dynamic\Foxy\Inventory\Extension;

use Dynamic\Foxy\Orders\Model\OrderDetail;
use SilverStripe\Forms\CheckboxField;
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
            CheckboxField::create('ControlInventory', 'Control Inventory?')
                ->setDescription('limit the number of this product available for purchase'),
            Wrapper::create(
                NumericField::create('PurchaseLimit')
                    ->setTitle('Number Available')
                    ->setDescription('add to cart form will be disabled once number available equals purchased'),
                ReadonlyField::create('NumberPurchased', 'Purchased', $this->getNumberPurchased())//,
            )->displayIf('ControlInventory')->isChecked()->end(),
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
            return $this->owner->PurchaseLimit > $this->getNumberPurchased();
        }
        return true;
    }

    /**
     * @return int
     */
    public function getNumberAvailable()
    {
        if ($this->getIsProductAvailable()) {
            return (int)$this->owner->PurchaseLimit - (int)$this->getNumberPurchased();
        }
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
     * @return DataList
     */
    public function getOrders()
    {
        if ($this->owner->ID) {
            return OrderDetail::get()->filter('ProductID', $this->owner->ID);
        }
        return false;
    }
}
