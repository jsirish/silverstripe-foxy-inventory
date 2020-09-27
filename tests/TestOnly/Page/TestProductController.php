<?php

namespace Dynamic\Foxy\Inventory\Test\TestOnly\Page;

use Dynamic\Foxy\Extension\PurchasableExtension;

/**
 * Class TestProductController
 * @package Dynamic\Foxy\Inventory\Test\TestOnly\Page
 */
class TestProductController extends \PageController
{
    /**
     * @var string[]
     */
    private static $extensions = [
        PurchasableExtension::class,
    ];
}
