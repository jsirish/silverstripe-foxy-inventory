<?php

namespace Dynamic\Foxy\Inventory\Test\TestOnly\Page;

use Dynamic\Foxy\Extension\Purchasable;
use Dynamic\Foxy\Inventory\Extension\ProductExpirationManager;
use Dynamic\Foxy\Inventory\Extension\ProductInventoryManager;
use Dynamic\Foxy\Model\Variation;
use SilverStripe\Dev\TestOnly;

/**
 * Class TestProduct
 * @package Dynamic\Foxy\Inventory\Test\TestOnly\Page
 */
class TestProduct extends \Page implements TestOnly
{
    /**
     * @var array
     */
    private static $extensions = [
        Purchasable::class,
        ProductInventoryManager::class,
        ProductExpirationManager::class,
    ];

    /**
     * @var string[]
     */
    private static $has_many = [
        'Variations' => Variation::class,
    ];
}
