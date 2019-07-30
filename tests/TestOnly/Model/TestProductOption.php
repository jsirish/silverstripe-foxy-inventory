<?php

namespace Dynamic\Foxy\Inventory\Test\TestOnly\Model;

use Dynamic\Foxy\Inventory\Extension\ProductOptionInventoryManager;
use Dynamic\Foxy\Model\ProductOption;

class TestProductOption extends ProductOption
{
    /**
     * @var array
     */
    private static $extensions = [
        ProductOptionInventoryManager::class,
    ];
}
