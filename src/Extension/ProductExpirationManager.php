<?php

namespace Dynamic\Foxy\Inventory\Extension;

use Dynamic\Foxy\Inventory\Model\CartReservation;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\ValidationResult;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class ProductExpirationManager extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'CartExpiration' => 'Boolean',
        'ExpirationMinutes' => 'Int',
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $expirationFields = [
            CheckboxField::create('CartExpiration')
                ->setTitle('Cart Product Expiration'),
            $duration = NumericField::create('ExpirationMinutes')
                ->setTitle('Expiration In Minutes')
                ->setDescription(
                    "After the time listed above in minutes, this product will be removed from the user's cart"
                ),
        ];
        $duration->displayIf('CartExpiration')->isChecked()->end();

        $fields->addFieldsToTab(
            'Root.Inventory',
            Wrapper::create(
                $expirationFields
            )->displayIf('Available')->isChecked()->end()
        );
    }

    /**
     * @param ValidationResult $validationResult
     */
    public function validate(ValidationResult $validationResult)
    {
        if ($this->owner->CartExpiration && $this->owner->ExpirationMinutes < 1) {
            $validationResult
                ->addFieldError(
                    'ExpirationMinutes',
                    'You must set the "Expiration In Minutes" or disable "Cart Product Expiration"'
                );
        }
    }
}
