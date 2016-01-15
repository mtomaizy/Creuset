<?php

namespace Creuset\Listeners;

use Creuset\Events\OrderWasPaid;
use Creuset\Events\ProductStockChanged;
use Creuset\Product;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReduceProductStock implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param OrderWasCompleted $event
     *
     * @return void
     */
    public function handle(OrderWasPaid $event)
    {
        foreach ($event->order->items as $item) {
            $this->reduceStock($item->orderable, $item->quantity);
        }
    }

    /**
     * Reduce the stock on a product.
     * 
     * @param Product $product
     * @param int     $quantity
     * 
     * @return bool
     */
    private function reduceStock(Product $product, $quantity)
    {
        $product->stock_qty -= $quantity;

        event(new ProductStockChanged($product));

        return $product->save();
    }
}