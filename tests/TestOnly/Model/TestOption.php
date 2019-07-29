<?php

namespace Dynamic\Foxy\Inventory\Test\TestOnly\Model;

use Dynamic\Foxy\Inventory\Extension\ProductOptionInventoryManager;
use Dynamic\Foxy\Model\ProductOption;
use SilverStripe\Dev\TestOnly;

/**
 * Class TestOption
 * @package Dynamic\FoxyStripe\Test\TestOnly
 *
 * @mixin FoxyStripeOptionInventoryManager
 */
class TestOption extends ProductOption implements TestOnly
{
    /**
     * @var string
     */
    private static $table_name = 'FoxyInventoryTestOption';

    /**
     * @var array
     */
    private static $extensions = [
        ProductOptionInventoryManager::class
    ];
}
