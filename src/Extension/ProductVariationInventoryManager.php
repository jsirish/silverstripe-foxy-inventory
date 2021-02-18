<?php

namespace Dynamic\Foxy\Inventory\Extension;

use Dynamic\Foxy\Orders\Model\OrderDetail;
use Dynamic\Foxy\Orders\Model\OrderVariation;
use SilverStripe\ORM\ArrayList;

/**
 * Class ProductVariationInventoryManager
 * @package Dynamic\Foxy\Inventory\Extension
 */
class ProductVariationInventoryManager extends ProductInventoryManager
{
    /**
     * @param $available
     */
    public function updateGetIsAvailable(&$available)
    {
        if ($this->getHasInventory()) {
            $available = $this->getIsProductAvailable();
        }
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
     * @return $this|ProductVariationInventoryManager
     */
    protected function setOrders()
    {
        if ($this->owner->ID) {
            if ($orderVariations = OrderVariation::get()->filter('VariationID', $this->owner->ID)) {
                $this->orders = OrderDetail::get()->byIDs($orderVariations->column());

                return $this;
            }
        }

        $this->orders = false;

        return $this;
    }
}
