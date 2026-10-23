<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * List every order placed on the storefront.
     */
    public function index(Request $request): Response
    {
        $orders = Order::query()
            ->with(['product', 'user', 'license'])
            ->orderByDesc('id')
            ->paginate(25);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => OrderResource::collection($orders),
        ]);
    }
}
