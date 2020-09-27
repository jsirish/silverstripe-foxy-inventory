<?php

namespace Dynamic\Foxy\Inventory\Test\Extension;

use Dynamic\Foxy\Extension\Purchasable;
use Dynamic\Foxy\Extension\PurchasableExtension;
use Dynamic\Foxy\Form\AddToCartForm;
use Dynamic\Foxy\Inventory\Extension\AddToCartFormExtension;
use Dynamic\Foxy\Inventory\Extension\ProductExpirationManager;
use Dynamic\Foxy\Inventory\Extension\ProductInventoryManager;
use Dynamic\Foxy\Inventory\Test\TestOnly\Extension\TestVariationDataExtension;
use Dynamic\Foxy\Inventory\Test\TestOnly\Page\TestProduct;
use Dynamic\Foxy\Inventory\Test\TestOnly\Page\TestProductController;
use Dynamic\Foxy\Model\Variation;
use SilverStripe\Dev\SapphireTest;

/**
 * Class AddToCartFormExtensionTest
 * @package Dynamic\Foxy\Inventory\Test\Extension
 */
class AddToCartFormExtensionTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = [
        '../products.yml',
    ];

    /**
     * @var array
     */
    protected static $extra_dataobjects = [
        TestProduct::class,
    ];

    /**
     * @var array
     */
    protected static $extra_controllers = [
        TestProductController::class,
    ];

    /**
     * @var array
     */
    protected static $required_extensions = [
        Variation::class => [
            TestVariationDataExtension::class,
        ],
        AddToCartForm::class => [
            AddToCartFormExtension::class,
        ],
        TestProduct::class => [
            Purchasable::class,
            ProductInventoryManager::class,
            ProductExpirationManager::class,
        ],
        TestProductController::class => [
            PurchasableExtension::class,
        ],
    ];

    /**
     *
     */
    public function testUpdateProductFields()
    {
        $object = $this->objFromFixture(TestProduct::class, 'productone');
        $controller = TestProductController::create($object);
        $form = AddToCartForm::create($controller, __FUNCTION__, null, null, null, $controller->data());
        $fields = $form->Fields();

        $this->assertNull($fields->dataFieldByName('expires'));
    }

    /**
     *
     */
    public function testIsOutOfStock()
    {
        $this->markTestSkipped();
        // todo: write test to test out of stock via fixtures
    }
}
