<?php

namespace Dynamic\Foxy\Inventory\Test\TestOnly\Page;

use Dynamic\Foxy\Extension\PurchasableExtension;

class TestProductController extends \PageController
{
    private static $extensions = [
        PurchasableExtension::class,
    ];
}
