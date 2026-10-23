<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\License;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CompleteOrder
{
    /**
     * Mark the order as paid and issue the matching license key.
     *
     * The action is idempotent: a callback that arrives twice never issues a
     * second license for the same order.
     */
    public function handle(Order $order, ?string $captureId = null): License
    {
        return DB::transaction(function () use ($order, $captureId): License {
            if ($order->status === OrderStatus::Paid && $order->license !== null) {
                return $order->license;
            }

            $order->update([
                'status' => OrderStatus::Paid,
                'paid_at' => $order->paid_at ?? now(),
                'paypal_capture_id' => $captureId ?? $order->paypal_capture_id,
            ]);

            $license = $order->license()->create([
                'product_id' => $order->product_id,
                'user_id' => $order->user_id,
                'key' => License::generateKey(),
                'activated_at' => now(),
            ]);

            $order->product()->increment('sales');

            return $license;
        });
    }
}
