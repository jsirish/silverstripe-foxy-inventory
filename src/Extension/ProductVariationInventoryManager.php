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
     * @return ArrayList|bool
     */
    public function getOrders()
    {
        if ($this->owner->ID) {
            $orderVariations = OrderVariation::get()->filter('VariationID', $this->owner->ID);
            if ($orderVariations) {
                $orders = ArrayList::create();
                foreach ($orderVariations as $orderVariation) {
                    $orderDetail = OrderDetail::get()->byID($orderVariation->OrderDetailID);
                    $orders->push($orderDetail);
                }
            }

            return isset($orders) ? $orders : false;
        }
        return false;
    }

    /**
     * @param $available
     */
    public function updateGetIsAvailable(&$available)
    {
        if ($this->getHasInventory()) {
            $available = $this->getIsProductAvailable();
        }
    }
}
