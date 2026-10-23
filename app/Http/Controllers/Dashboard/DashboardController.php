<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Number;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Overview of the apps owned by the current user and their sales.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $productIds = $user->products()->withTrashed()->pluck('id');

        $paidOrders = Order::query()
            ->whereIn('product_id', $productIds)
            ->where('status', OrderStatus::Paid);

        $revenue = (int) (clone $paidOrders)->sum('amount');

        return Inertia::render('Dashboard', [
            'stats' => [
                'products' => $user->products()->withTrashed()->count(),
                'published' => $user->products()->published()->count(),
                'sales' => (clone $paidOrders)->count(),
                'revenue' => $revenue,
                'revenue_formatted' => Number::currency(round($revenue / 100, 2), 'USD'),
                'views' => (int) $user->products()->withTrashed()->sum('views'),
            ],
            'recentOrders' => OrderResource::collection(
                Order::query()
                    ->whereIn('product_id', $productIds)
                    ->with(['product', 'user'])
                    ->orderByDesc('id')
                    ->limit(8)
                    ->get()
            ),
            'topProducts' => ProductResource::collection(
                $user->products()
                    ->withTrashed()
                    ->with(['user', 'tags'])
                    ->orderByDesc('sales')
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get()
            ),
        ]);
    }
}
