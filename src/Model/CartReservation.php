<?php

namespace Dynamic\Foxy\Inventory\Model;

use Dynamic\Foxy\Model\FoxyHelper;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;

class CartReservation extends DataObject
{
    /**
     * @var string
     */
    private static $singular_name = 'Cart Reservation';

    /**
     * @var string
     */
    private static $plural_name = 'Cart Reservations';

    /**
     * @var string
     */
    private static $table_name = 'FoxyCartReservation';

    /**
     * @var array
     */
    private static $db = [
        //'ReservationCode' => 'Varchar(255)',
        'ProductID' => 'Int',
        'CartProductID' => 'Int',
        'Code' => 'Varchar(255)',
        'Expires' => 'DBDatetime',
        'Cart' => 'Varchar(255)',
    ];

    /**
     * @var array
     */
    /*private static $indexes = [
        'foxy-reservation-code' => [
            'type' => 'unique',
            'columns' => ['ReservationCode'],
        ],
    ];//*/

    /**
     * @var array
     */
    private static $summary_fields = [
        'ReservationCode' => 'Reservation',
        'Expires.Nice' => 'Expires',
    ];

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->addFieldToTab('Root.Main', ReadonlyField::create('ReservationCode')->setTitle('Reservation Code'));
        });
        return parent::getCMSFields();
    }

    /**
     *
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->ProductID) {
            $helper = FoxyHelper::create();
            if ($product = $helper->getProducts()->filter('Code', $this->Code)->first()) {
                $this->ProductID = $product->ID;
            }
        }
    }

    /**
     * @param null $member
     * @param array $context
     * @return bool
     */
    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    /**
     * @param null $member
     * @return bool
     */
    public function canEdit($member = null)
    {
        return false;
    }

    /**
     * @param null $member
     * @return bool|int
     */
    public function canDelete($member = null)
    {
        return Permission::check('ADMIN', 'any', Security::getCurrentUser());
    }

    /**
     * @param null $member
     * @return bool
     */
    public function canView($member = null)
    {
        return true;
    }
}
