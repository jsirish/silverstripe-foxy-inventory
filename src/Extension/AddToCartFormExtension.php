<?php

namespace Dynamic\Foxy\Inventory\Extension;

use Dynamic\Foxy\Form\AddToCartForm;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;

/**
 * Class AddToCartFormExtension
 * @package Dynamic\Foxy\Inventory\Extension
 */
class AddToCartFormExtension extends Extension
{
    /**
     * @param FieldList $fields
     */
    public function updateProductFields(FieldList &$fields)
    {
        if ($this->owner->getProduct()->CartExpiration) {
            $fields->insertAfter(
                'url',
                HiddenField::create('expires')
                    ->setValue(
                        AddToCartForm::getGeneratedValue(
                            $this->owner->getProduct()->Code,
                            'expires',
                            $this->owner->getProduct()->ExpirationMinutes,
                            'value'
                        )
                    )
            );
        }
    }
}
