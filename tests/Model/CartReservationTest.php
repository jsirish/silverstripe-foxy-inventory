<?php

namespace Dynamic\Foxy\Inventory\Test\Model;

use Dynamic\Foxy\Inventory\Model\CartReservation;
use SilverStripe\Core\Config\Config;
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
    protected function setUp()
    {
        parent::setUp();

        Config::modify()->set('Dynamic\\Foxy\\SingleSignOn\\Client\\CustomerClient', 'foxy_sso_enabled', false);
    }

    /**
     *
     */
    public function testGetCMSFields()
    {
        $object = CartReservation::singleton();
        $fields = $object->getCMSFields();
        $this->assertInstanceOf(FieldList::class, $fields);
    }
}
