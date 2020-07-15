<?php

namespace Dynamic\Foxy\Inventory\Test\Extension;

use Dynamic\Foxy\Extension\Purchasable;
use Dynamic\Foxy\Inventory\Extension\ProductExpirationManager;
use Dynamic\Foxy\Inventory\Extension\ProductInventoryManager;
use Dynamic\Foxy\Inventory\Extension\ProductVariationInventoryManager;
use Dynamic\Foxy\Inventory\Test\TestOnly\Model\TestProductOption;
use Dynamic\Foxy\Inventory\Test\TestOnly\Model\TestVariation;
use Dynamic\Foxy\Inventory\Test\TestOnly\Page\TestProduct;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;



class ProductVariationInventoryManagerTest extends SapphireTest
{
    /**
     * @var array
     */
    protected static $fixture_file = '../fixtures.yml';

    /**
     * @var array
     */
    protected static $extra_dataobjects = [
        TestProduct::class,
        TestVariation::class,
    ];

    /**
     * @var array
     */
    protected static $required_extensions = [
        TestProduct::class => [
            Purchasable::class,
            ProductInventoryManager::class,
            ProductExpirationManager::class,
        ],
        TestVariation::class => [
            ProductVariationInventoryManager::class,
        ],
    ];

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        Config::modify()->set('Dynamic\\Foxy\\SingleSignOn\\Client\\CustomerClient', 'foxy_sso_enabled', false);
    }

    /**
     *
     */
    public function testUpdateCMSFields()
    {
        $object = Injector::inst()->create(TestProduct::class);
        $fields = $object->getCMSFields();
        $this->assertInstanceOf(FieldList::class, $fields);
    }

    /**
     *
     */
    public function testGetHasInventory()
    {
        /** @var TestProductOption $option */
        $option = Injector::inst()->create(TestVariation::class);
        $option->ControlInventory = false;
        $option->PurchaseLimit = 0;
        $this->assertFalse($option->getHasInventory());
        $option->ControlInventory = true;
        $option->PurchaseLimit = 0;
        $this->assertFalse($option->getHasInventory());
        $option->ControlInventory = false;
        $option->PurchaseLimit = 10;
        $this->assertFalse($option->getHasInventory());
        $option->ControlInventory = true;
        $option->PurchaseLimit = 10;
        $this->assertTrue($option->getHasInventory());
    }

    /**
     *
     */
    public function testGetIsOptionAvailable()
    {
        $this->markTestSkipped();
        /** @var TestProductOption $option */
        $option = $this->objFromFixture(TestProductOption::class, 'one');
        // no inventory control
        $option->ControlInventory = false;
        $option->PurchaseLimit = 0;
        $this->assertTrue($option->getIsOptionAvailable());
        // inventory control, no limit
        $option->ControlInventory = true;
        $option->PurchaseLimit = 0;
        $this->assertTrue($option->getIsOptionAvailable());
        // inventory control, with limit
        $option->ControlInventory = true;
        $option->PurchaseLimit = 10;
        $this->assertTrue($option->getIsOptionAvailable());
        /** @var OrderDetail $detail */
        $detail = OrderDetail::create();
        $detail->Quantity = 10;
        $detail->write();
        $detail->OptionItems()->add($option);
        // inventory control, no inventory left
        $option->ControlInventory = true;
        $option->PurchaseLimit = 10;
        $this->assertFalse($option->getIsOptionAvailable());
        $detail->delete();
    }

    /**
     *
     */
    public function testGetNumberPurchased()
    {
        $this->markTestSkipped();
        /** @var TestProductOption $option */
        $option = $this->objFromFixture(TestProductOption::class, 'one');
        $this->assertEquals(0, $option->getNumberPurchased());
        /** @var OrderDetail $detail */
        $detail = OrderDetail::create();
        $detail->Quantity = 10;
        $detail->write();
        $detail->OptionItems()->add($option);
        $this->assertEquals(10, $option->getNumberPurchased());
        $detail->delete();
    }

    /**
     *
     */
    public function testGetOrders()
    {
        $this->markTestSkipped();
        /** @var TestProductOption $option */
        $option = $this->objFromFixture(TestProductOption::class, 'one');
        $this->assertEquals(0, $option->getOrders()->Count());
        /** @var OrderDetail $detail */
        $detail = OrderDetail::create();
        $detail->Quantity = 10;
        $detail->write();
        $detail->OptionItems()->add($option);
        $this->assertEquals(1, $option->getOrders()->count());
        $detail->delete();
    }
}
