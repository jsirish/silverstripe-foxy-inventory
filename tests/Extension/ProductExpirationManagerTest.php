<?php

namespace Dynamic\Foxy\Inventory\Test\Extension;

use Dynamic\Foxy\Inventory\Test\TestOnly\Page\TestProduct;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;

class ProductExpirationManagerTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures.yml';

    /**
     * @var array
     */
    protected static $extra_dataobjects = [
        TestProduct::class,
    ];

    /**
     *
     */
    public function testGetCMSFields()
    {
        $object = $this->objFromFixture(TestProduct::class, 'one');
        $fields = $object->getCMSFields();
        $this->assertInstanceOf(FieldList::class, $fields);
    }
}
