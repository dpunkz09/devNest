<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class RefundOrder
{
    /**
     * Take the payment back and revoke the license that came with it.
     *
     * A paid order becomes refunded: the license is revoked and the sale is
     * taken off the sales counter of the app. An order that was still pending
     * never became a sale, so it is cancelled instead.
     *
     * The action is idempotent: a refund that is reported twice never
     * revokes a license or lowers the sales counter twice.
     */
    public function handle(Order $order, ?string $refundId = null): Order
    {
        return DB::transaction(function () use ($order, $refundId): Order {
            if ($order->status === OrderStatus::Refunded) {
                return $order;
            }

            $wasPaid = $order->status === OrderStatus::Paid;

            $order->update([
                'status' => $wasPaid ? OrderStatus::Refunded : OrderStatus::Cancelled,
                'refunded_at' => $wasPaid ? ($order->refunded_at ?? now()) : $order->refunded_at,
                'paypal_refund_id' => $refundId ?? $order->paypal_refund_id,
            ]);

            $license = $order->license;

            if ($license !== null && $license->revoked_at === null) {
                $license->update(['revoked_at' => now()]);
            }

            if ($wasPaid) {
                $this->removeSale($order);
            }

            return $order->refresh();
        });
    }

    /**
     * Take the sale off the counter of the app.
     *
     * The counter is an unsigned column, so it is only lowered when it still
     * holds a sale. That keeps the counter and the refunds in step even when
     * an order was settled more than once.
     */
    protected function removeSale(Order $order): void
    {
        $product = $order->product;

        if ($product !== null && $product->sales > 0) {
            $product->decrement('sales');
        }
    }
}
