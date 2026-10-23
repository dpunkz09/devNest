<?php

namespace App\Exceptions;

use App\Models\Product;
use RuntimeException;

class CannotPurchaseOwnProduct extends RuntimeException
{
    /**
     * Build the exception for an app a developer tried to buy.
     */
    public static function for(Product $product): self
    {
        return new self("A developer cannot purchase their own app [{$product->slug}].");
    }
}
