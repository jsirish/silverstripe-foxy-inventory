<?php

namespace Dynamic\Foxy\Inventory\Extension;

use Dynamic\Foxy\Inventory\Model\CartReservation;
use Dynamic\Foxy\Model\FoxyHelper;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;

class ProductControllerExtension extends Extension
{
    /**
     *
     */
    public function onAfterInit()
    {
        Requirements::javascript('dynamic/silverstripe-foxy-inventory: client/dist/javascript/inventory.js');
    }

    /**
     * @var array
     */
    private static $allowed_actions = [
        'reserveproduct' => '->validReservation',
    ];

    /**
     * @param HTTPRequest|null $request
     * @return bool
     */
    public function validReservation(HTTPRequest $request = null)
    {
        if (!$request instanceof HTTPRequest) {
            $request = Controller::curr()->getRequest();
        }

        return true;
    }

    /**
     * @param HTTPRequest $request
     */
    public function reserveproduct(HTTPRequest $request)
    {
        $code = $request->getVar('code');
        $id = $request->getVar('id');
        $expires = $request->getVar('expires');
        $quantity = $request->getVar('quantity');
        $cart = $request->getVar('cart');

        if (!$code || !$id || !$expires || !$quantity || !$cart) {
            return false;
        }

        if (!$this->isProductReserved($code, $id, $expires)) {
            $this->addProductReservation($code, $id, $expires, $quantity, $cart);
        }
    }

    /**
     * @param $code
     * @param $id
     * @param $expires
     * @param $quantity
     * @param $cart
     * @throws \SilverStripe\ORM\ValidationException
     */
    protected function addProductReservation($code, $id, $expires, $quantity, $cart)
    {
        $codeFilter = function (\Page $page) use ($code) {
            return $page->Code == $code;
        };

        if ($product = FoxyHelper::singleton()->getProducts()->filterByCallback($codeFilter)->first()) {
            $existing = CartReservation::get()
                ->filter([
                    'Cart' => $cart,
                    'Code' => $code,
                    'CartProductID' => $id,
                    'ProductID' => $product->ID
                ]);

            $remaining = $quantity - $existing->count();

            if ($remaining > 0) {
                for ($i = 0; $i < $remaining; $i++) {
                    $reservation = CartReservation::create();
                    $reservation->ReservationCode = $this->getReservationHash($code, $id, $expires);
                    $reservation->CartProductID = $id;
                    $reservation->Code = $code;
                    $reservation->Expires = date('Y-m-d H:i:s', $expires);
                    $reservation->ProductID = $product->ID;

                    $reservation->write();
                }
            }
        }
    }

    /**
     * @param $code
     * @param $id
     * @param $expires
     * @return \SilverStripe\ORM\DataObject
     */
    protected function isProductReserved($code, $id, $expires)
    {
        return CartReservation::get()->filter('ReservationCode', $this->getReservationHash($code, $id, $expires))->first();
    }

    /**
     * @param $code
     * @param $id
     * @param $expires
     * @return string
     */
    protected function getReservationHash($code, $id, $expires)
    {
        return md5($code . $id . $expires);
    }
}
