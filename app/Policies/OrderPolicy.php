<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can review the given order.
     *
     * Both the buyer and the developer who sold the app may look at an order.
     */
    public function view(User $user, Order $order): bool
    {
        if ($order->user_id === $user->id) {
            return true;
        }

        return $order->product()->where('user_id', $user->id)->exists();
    }
}
