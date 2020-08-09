<?php

namespace Dynamic\Foxy\Inventory\Task;

use Dynamic\Foxy\Model\FoxyHelper;
use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;

/**
 * Class InventoryTrackingTask
 * @package Dynamic\Foxy\Inventory\Task
 */
class InventoryTrackingTask extends BuildTask
{
    /**
     * @var string
     */
    protected $title = 'Foxy Inventory - Inventory Tracking Task';

    /**
     * @var string
     */
    protected $description = 'Calculate total number of purchases for products and variations';

    /**
     * @var string
     */
    private static $segment = 'foxy-inventory-tracking-task';

    /**
     * @param HTTPRequest $request
     */
    public function run($request)
    {
        $this->updateProducts();
    }

    /**
     *
     */
    public function updateProducts()
    {
        $helper = FoxyHelper::create();
        $products = $helper->getProducts();

        foreach ($products as $product) {
            $number_purchased_current = $product->NumberPurchased;
            $product->NumberPurchased = $product->getNumberPurchasedUpdate();
            $product->InventorySync = date("Y-m-d H:i:s");
            if ($number_purchased_current != $product->NumberPurchased && $product->InventorySync > strtotime( $product->InventorySync ) + 600) {
                $product->write();
                static::write_message($product->Title . ' number purchased updated to ' . $product->NumberPurchased);
            }

            if ($product->Variations()->count()) {
                foreach ($product->Variations() as $variation) {
                    $variation->NumberPurchasedCurrent = $variation->NumberPurchased;
                    $variation->NumberPurchased = $variation->getNumberPurchasedUpdate();
                    if ($variation->NumberPurchasedCurrent != $variation->NumberPurchased) {
                        $variation->write();
                        static::write_message($variation->Title . ' number purchased updated to ' . $variation->NumberPurchased);
                    }
                }
            }
        }
    }

    /**
     * @param $message
     */
    protected static function write_message($message)
    {
        if (Director::is_cli()) {
            echo "'{$message}\n";
        } else {
            echo "{$message}<br><br>";
        }
    }
}
