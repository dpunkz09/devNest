<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Number;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Overview of the whole storefront.
     */
    public function index(Request $request): Response
    {
        $paidOrders = Order::query()->where('status', OrderStatus::Paid);

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users' => User::query()->count(),
                'admins' => User::query()->where('is_admin', true)->count(),
                'products' => Product::query()->withTrashed()->count(),
                'published' => Product::query()->where('status', 'published')->count(),
                'orders' => Order::query()->count(),
                'paid_orders' => (clone $paidOrders)->count(),
                'revenue' => (int) (clone $paidOrders)->sum('amount'),
                'revenue_formatted' => Number::currency(
                    round((int) (clone $paidOrders)->sum('amount') / 100, 2),
                    'USD',
                ),
            ],
            'recentUsers' => UserResource::collection(
                User::query()->orderByDesc('id')->limit(8)->get(),
            ),
            'recentOrders' => OrderResource::collection(
                Order::query()
                    ->with(['product', 'user'])
                    ->orderByDesc('id')
                    ->limit(8)
                    ->get(),
            ),
            'topProducts' => ProductResource::collection(
                Product::query()
                    ->with(['user', 'tags'])
                    ->withTrashed()
                    ->orderByDesc('sales')
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get(),
            ),
        ]);
    }
}
