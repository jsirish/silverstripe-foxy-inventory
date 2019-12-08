<?php

namespace Dynamic\Foxy\Inventory\Extension;

use Dynamic\Foxy\Form\AddToCartForm;
use Dynamic\Foxy\Inventory\Model\CartReservation;
use Dynamic\Foxy\Model\FoxyHelper;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HiddenField;

class AddToCartFormExtension extends Extension
{
    /**
     * @param \SilverStripe\Forms\FieldList $fields
     */
    public function updateProductFields(FieldList &$fields)
    {
        if ($this->owner->getProduct()->CartExpiration) {
            $fields->insertAfter(
                'code',
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

        if ($this->isOutOfStock()) {
            $fields = FieldList::create(
                HeaderField::create('OutOfStock', 'Out of stock')
                    ->setHeadingLevel(3)
            );
        }
    }

    /**
     * @param \SilverStripe\Forms\FieldList $actions
     */
    public function updateProductActions(FieldList &$actions)
    {
        if ($this->isOutOfStock()) {
            $actions = FieldList::create();
        }
    }

    /**
     * @return bool
     */
    public function isOutOfStock()
    {
        if (!$this->owner->getProduct()->ControlInventory) {
            return false;
        }
        $reserved = CartReservation::get()
            ->filter([
                'Code' => $this->owner->getProduct()->Code,
                'Expires:GreaterThan' => date('Y-m-d H:i:s', strtotime('now')),
            ])->count();
        $sold = $this->owner->getProduct()->getNumberPurchased();

        if ($reserved + $sold >= $this->owner->getProduct()->PurchaseLimit) {
            return true;
        }

        return false;
    }
}
