<?php

namespace Dynamic\Foxy\Inventory\Test\Model;

use Dynamic\Foxy\Inventory\Model\CartReservation;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;

class CartReservationTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures.yml';

    /**
     *
     */
    public function testGetCMSFields()
    {
        $object = singleton(CartReservation::class);
        $fields = $object->getCMSFields();
        $this->assertInstanceOf(FieldList::class, $fields);
    }
}
