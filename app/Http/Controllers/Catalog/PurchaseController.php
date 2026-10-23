<?php

namespace App\Http\Controllers\Catalog;

use App\Actions\CompleteOrder;
use App\Actions\CreateOrder;
use App\Actions\StartPayPalCheckout;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Exceptions\CannotPurchaseOwnProduct;
use App\Exceptions\PayPalException;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Services\PayPal\PayPalClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PurchaseController extends Controller
{
    /**
     * Start the checkout for the given app.
     */
    public function store(
        Request $request,
        Product $product,
        CreateOrder $createOrder,
        CompleteOrder $completeOrder,
        StartPayPalCheckout $checkout,
        PayPalClient $paypal,
    ): SymfonyResponse {
        abort_unless($product->status === ProductStatus::Published, 404);

        $existing = $request->user()
            ->orders()
            ->where('product_id', $product->id)
            ->whereIn('status', [OrderStatus::Pending->value, OrderStatus::Paid->value])
            ->first();

        if ($existing?->status === OrderStatus::Paid) {
            return to_route('purchases.show', $existing);
        }

        try {
            $order = $existing ?? $createOrder->handle($request->user(), $product);
        } catch (CannotPurchaseOwnProduct) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'You already own this app.']);

            return to_route('apps.show', $product);
        }

        if ($product->is_free) {
            $completeOrder->handle($order);

            return to_route('purchases.show', $order);
        }

        return $this->redirectToPayPal($order, $checkout, $paypal);
    }

    /**
     * List every purchase made by the current user.
     */
    public function index(Request $request): Response
    {
        $orders = $request->user()
            ->orders()
            ->with(['product.user', 'license'])
            ->orderByDesc('id')
            ->paginate(10)
            ->through(fn (Order $order): OrderResource => new OrderResource($order));

        return Inertia::render('Purchases/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Review a single purchase.
     */
    public function show(Request $request, Order $order): Response
    {
        $this->authorizeOrder($request, $order);

        $order->load(['product.user', 'product.features', 'license']);

        return Inertia::render('Purchases/Show', [
            'order' => new OrderResource($order),
        ]);
    }

    /**
     * Send the buyer to PayPal to pay for an order that is still pending.
     */
    public function pay(Request $request, Order $order, StartPayPalCheckout $checkout, PayPalClient $paypal): SymfonyResponse
    {
        $this->authorizeOrder($request, $order);

        if ($order->status === OrderStatus::Paid) {
            return to_route('purchases.show', $order);
        }

        return $this->redirectToPayPal($order, $checkout, $paypal);
    }

    /**
     * Hand the buyer over to the download of the purchased app.
     */
    public function download(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        $order->load(['product', 'license']);

        if ($order->status !== OrderStatus::Paid) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'This order has not been paid yet.']);

            return back();
        }

        if ($order->license?->revoked) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'This license was revoked after a refund.']);

            return back();
        }

        if (blank($order->product->download_url)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'The seller has not published a download link yet.']);

            return back();
        }

        $order->license?->increment('downloads');

        return redirect()->away($order->product->download_url);
    }

    /**
     * Create the PayPal order and send the buyer to the approval page.
     */
    protected function redirectToPayPal(Order $order, StartPayPalCheckout $checkout, PayPalClient $paypal): SymfonyResponse
    {
        if (! $paypal->isConfigured()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Payments are not available yet. Please try again later.']);

            return to_route('purchases.show', $order);
        }

        try {
            $approvalUrl = $checkout->handle($order);
        } catch (PayPalException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return to_route('purchases.show', $order);
        }

        return Inertia::location($approvalUrl);
    }

    /**
     * Make sure the order belongs to the current user.
     */
    protected function authorizeOrder(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 403);
    }
}
