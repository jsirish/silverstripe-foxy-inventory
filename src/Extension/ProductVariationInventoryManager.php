<?php

namespace Dynamic\Foxy\Inventory\Extension;

use Dynamic\Foxy\Orders\Model\OrderDetail;
use Dynamic\Foxy\Orders\Model\OrderVariation;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;

class ProductVariationInventoryManager extends ProductInventoryManager
{
    /**
     * @return DataList
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

            return $orders;

        }
        return false;
    }

    /**
     * @param $available
     */
    public function updateAvailability(&$available)
    {
        if ($this->getHasInventory()) {
            $available = $this->getIsProductAvailable();
        }
    }
}
