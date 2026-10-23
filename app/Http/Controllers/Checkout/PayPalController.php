<?php

namespace App\Http\Controllers\Checkout;

use App\Actions\CompleteOrder;
use App\Actions\RefundOrder;
use App\Enums\OrderStatus;
use App\Exceptions\PayPalException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PayPal\PayPalClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayPalController extends Controller
{
    /**
     * The buyer comes back from PayPal and the order is captured.
     */
    public function return(Request $request, PayPalClient $paypal, CompleteOrder $completeOrder): RedirectResponse
    {
        $order = $this->findOrder($request);

        if ($order->status === OrderStatus::Paid) {
            return to_route('purchases.show', $order);
        }

        try {
            $capture = $paypal->captureOrder((string) $order->paypal_order_id);
        } catch (PayPalException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return to_route('purchases.show', $order);
        }

        $completeOrder->handle($order, $this->captureId($capture));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Payment complete. Your license key is ready.']);

        return to_route('purchases.show', $order);
    }

    /**
     * The buyer abandoned the checkout on the PayPal website.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $order = $this->findOrder($request);

        Inertia::flash('toast', ['type' => 'info', 'message' => 'Payment cancelled. You can pay for this app whenever you like.']);

        return to_route('purchases.show', $order);
    }

    /**
     * PayPal notifies the storefront about a settled payment.
     */
    public function webhook(
        Request $request,
        PayPalClient $paypal,
        CompleteOrder $completeOrder,
        RefundOrder $refundOrder,
    ): JsonResponse {
        $payload = $request->all();

        if (! $paypal->verifyWebhookSignature($request, $payload)) {
            return response()->json(['message' => 'The webhook signature could not be verified.'], 400);
        }

        $this->handleWebhookEvent($payload, $completeOrder, $refundOrder);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Act on the event PayPal reported.
     *
     * A settled capture becomes a paid order with a license. A refunded or
     * reversed capture takes the payment back and revokes the license.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function handleWebhookEvent(
        array $payload,
        CompleteOrder $completeOrder,
        RefundOrder $refundOrder,
    ): void {
        $eventType = $payload['event_type'] ?? null;

        if (! in_array($eventType, [
            'PAYMENT.CAPTURE.COMPLETED',
            'PAYMENT.CAPTURE.REFUNDED',
            'PAYMENT.CAPTURE.REVERSED',
        ], true)) {
            return;
        }

        $order = $this->findOrderForWebhook($payload);

        if ($order === null) {
            return;
        }

        $resource = $payload['resource'] ?? [];

        if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            if ($order->status !== OrderStatus::Paid) {
                $completeOrder->handle($order, $this->captureId($resource));
            }

            return;
        }

        $refundOrder->handle($order, $this->refundId($resource));
    }

    /**
     * Find the order the buyer paid for.
     */
    protected function findOrder(Request $request): Order
    {
        $token = (string) $request->query('token', '');

        $order = Order::query()
            ->where('paypal_order_id', $token)
            ->first();

        abort_if($order === null, 404);
        abort_unless($order->user_id === $request->user()->id, 403);

        return $order;
    }

    /**
     * Find the order a webhook call belongs to.
     *
     * The PayPal order id is the most reliable match. When it is missing or
     * unknown, the id of the local order that was sent along as the custom id
     * is used instead.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function findOrderForWebhook(array $payload): ?Order
    {
        $resource = $payload['resource'] ?? [];

        $paypalOrderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;
        $localOrderId = $resource['purchase_units'][0]['custom_id'] ?? null;

        if (filled($paypalOrderId)) {
            $order = Order::query()
                ->where('paypal_order_id', $paypalOrderId)
                ->first();

            if ($order !== null) {
                return $order;
            }
        }

        if (filled($localOrderId)) {
            return Order::query()->find((int) $localOrderId);
        }

        return null;
    }

    /**
     * The id of the capture inside a PayPal response.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function captureId(array $payload): ?string
    {
        $captures = $payload['purchase_units'][0]['payments']['captures'] ?? [];

        return $captures[0]['id'] ?? $payload['id'] ?? null;
    }

    /**
     * The id of the refund inside a PayPal response.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function refundId(array $payload): ?string
    {
        $refunds = $payload['refunds'] ?? [];

        return $payload['id'] ?? $refunds[0]['id'] ?? null;
    }
}
