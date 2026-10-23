<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Exceptions\CannotPurchaseOwnProduct;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateOrder
{
    /**
     * Place a pending order for the given product.
     *
     * @throws CannotPurchaseOwnProduct
     */
    public function handle(User $buyer, Product $product): Order
    {
        if ($product->user_id === $buyer->id) {
            throw CannotPurchaseOwnProduct::for($product);
        }

        return DB::transaction(fn (): Order => $buyer->orders()->create([
            'product_id' => $product->id,
            'reference' => Order::generateReference(),
            'amount' => $product->price,
            'currency' => $product->currency,
            'status' => OrderStatus::Pending,
        ]));
    }
}
