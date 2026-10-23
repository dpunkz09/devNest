<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Sales made for the apps owned by the current user.
     */
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', Rule::enum(OrderStatus::class)],
        ]);

        $productIds = $request->user()->products()->withTrashed()->pluck('id');

        $orders = Order::query()
            ->whereIn('product_id', $productIds)
            ->with(['product.user', 'user', 'license'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Order $order): OrderResource => new OrderResource($order));

        return Inertia::render('Dashboard/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'status' => $filters['status'] ?? null,
            ],
            'statuses' => OrderStatus::selectOptions(),
        ]);
    }

    /**
     * Detail of a single sale.
     */
    public function show(Request $request, Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load(['product.user', 'user', 'license']);

        return Inertia::render('Dashboard/Orders/Show', [
            'order' => new OrderResource($order),
        ]);
    }
}
