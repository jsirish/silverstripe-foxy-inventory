<?php

namespace Dynamic\Foxy\Inventory\Test\TestOnly\Form;

use Dynamic\Foxy\Form\AddToCartForm;
use Dynamic\Foxy\Inventory\Extension\AddToCartFormExtension;
use Dynamic\Foxy\Model\FoxyHelper;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\RequiredFields;

class TestAddToCartForm extends AddToCartForm
{
    /**
     * @var array
     */
    private static $extensions = [
        AddToCartFormExtension::class,
    ];

    public function __construct(
        $controller,
        $name,
        FieldList $fields = null,
        FieldList $actions = null,
        $validator = null,
        $product = null,
        $helper = null
    ) {
        $this->setProduct($product);
        $this->setFoxyHelper($helper);

        $fields = ($fields != null && $fields->exists()) ?
            $this->getProductFields($fields) :
            $this->getProductFields(FieldList::create());

        $actions = ($actions != null && $actions->exists()) ?
            $this->getProductActions($actions) :
            $this->getProductActions(FieldList::create());

        $validator = (!empty($validator) || $validator != null) ? $validator : RequiredFields::create();

        parent::__construct($controller, $name, $fields, $actions, $validator);

        //have to call after parent::__construct()
        $this->setAttribute('action', FoxyHelper::FormActionURL());
        $this->disableSecurityToken();
        $this->setHTMLID($this->getTemplateHelper()->generateFormID($this) . "_{$product->ID}");
    }
}
