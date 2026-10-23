<?php

namespace App\Actions;

use App\Models\Order;
use App\Services\PayPal\PayPalClient;

class StartPayPalCheckout
{
    public function __construct(protected PayPalClient $paypal) {}

    /**
     * Create a PayPal order for the given order and return the approval url.
     */
    public function handle(Order $order): string
    {
        $order->loadMissing('product');

        $paypalOrder = $this->paypal->createOrder(
            $order,
            route('paypal.return'),
            route('paypal.cancel'),
        );

        $order->update(['paypal_order_id' => $paypalOrder['id']]);

        return $paypalOrder['approval_url'];
    }
}
