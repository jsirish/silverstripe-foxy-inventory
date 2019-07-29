<?php

namespace Dynamic\Foxy\Inventory\Test\Extension;

use Dynamic\Foxy\Inventory\Test\TestOnly\Page\TestProduct;
use Dynamic\Foxy\Inventory\Test\TestOnly\Page\TestProductController;
use Dynamic\Foxy\Orders\Model\Order;
use Dynamic\Foxy\Orders\Model\OrderDetail;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;

class ProductInventoryManagerTest extends SapphireTest
{
    /**
     * @var array
     */
    protected static $fixture_file = array(
        '../fixtures.yml',
    );

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
     *
     */
    public function testUpdateCMSFields()
    {
        $object = $this->objFromFixture(TestProduct::class, 'one');
        $fields = $object->getCMSFields();
        $this->assertInstanceOf(FieldList::class, $fields);
        $this->assertNotNull($fields->dataFieldByName('ControlInventory'));
    }

    /**
     *
     */
    public function testFoxyStripePurchaseForm()
    {
        /** @var TestProduct $object */
        $object = $this->objFromFixture(TestProduct::class, 'one');
        /** @var TestProductController $controller */
        $controller = TestProductController::create($object);
        $form = $controller->AddToCartForm();
        $this->assertInstanceOf(Form::class, $form);
    }

    /**
     *
     */
    public function testGetHasInventory()
    {
        /** @var TestProduct $product */
        $product = $this->objFromFixture(TestProduct::class, 'one');
        $product->ControlInventory = false;
        $product->PurchaseLimit = 0;
        $this->assertFalse($product->getHasInventory());
        $product->ControlInventory = true;
        $product->PurchaseLimit = 0;
        $this->assertFalse($product->getHasInventory());
        $product->ControlInventory = false;
        $product->PurchaseLimit = 10;
        $this->assertFalse($product->getHasInventory());
        $product->ControlInventory = true;
        $product->PurchaseLimit = 10;
        $this->assertTrue($product->getHasInventory());
    }

    /**
     *
     */
    public function testGetIsProductAvailable()
    {
        /** @var TestProduct $product */
        $product = $this->objFromFixture(TestProduct::class, 'one');
        // no inventory control
        $product->ControlInventory = false;
        $product->PurchaseLimit = 0;
        $this->assertTrue($product->getIsProductAvailable());
        // inventory control, no limit
        $product->ControlInventory = true;
        $product->PurchaseLimit = 0;
        $this->assertTrue($product->getIsProductAvailable());
        // inventory control, with limit
        $product->ControlInventory = true;
        $product->PurchaseLimit = 10;
        $this->assertTrue($product->getIsProductAvailable());
        /** @var OrderDetail $detail */
        $detail = OrderDetail::create();
        $detail->OrderID = $this->objFromFixture(Order::class, 'one')->ID;
        $detail->Quantity = 10;
        $detail->ProductID = $product->ID;
        $detail->write();
        // inventory control, no inventory left
        $product->ControlInventory = true;
        $product->PurchaseLimit = 10;
        $this->assertFalse($product->getIsProductAvailable());
    }

    /**
     *
     */
    public function testGetNumberPurchased()
    {
        /** @var TestProduct $product */
        $product = $this->objFromFixture(TestProduct::class, 'one');
        $this->assertEquals(0, $product->getNumberPurchased());
        /** @var OrderDetail $detail */
        $detail = OrderDetail::create();
        $detail->OrderID = $this->objFromFixture(Order::class, 'one')->ID;
        $detail->Quantity = 10;
        $detail->ProductID = $product->ID;
        $detail->write();
        $this->assertEquals(10, $product->getNumberPurchased());
    }

    /**
     *
     */
    public function testGetOrders()
    {
        /** @var TestProduct $product */
        $product = $this->objFromFixture(TestProduct::class, 'one');
        $this->assertEquals(0, $product->getOrders()->Count());
        /** @var OrderDetail $detail */
        $detail = OrderDetail::create();
        $detail->OrderID = $this->objFromFixture(Order::class, 'one')->ID;
        $detail->Quantity = 10;
        $detail->ProductID = $product->ID;
        $detail->write();
        $this->assertEquals(1, $product->getOrders()->count());
    }
}
