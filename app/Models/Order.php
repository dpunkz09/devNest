<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property string $reference
 * @property int $amount
 * @property string $currency
 * @property string|null $paypal_order_id
 * @property string|null $paypal_capture_id
 * @property string|null $paypal_refund_id
 * @property OrderStatus $status
 * @property Carbon|null $paid_at
 * @property Carbon|null $refunded_at
 * @property-read string $amount_formatted
 */
#[Fillable(['user_id', 'product_id', 'reference', 'amount', 'currency', 'status', 'paid_at', 'refunded_at', 'paypal_order_id', 'paypal_capture_id', 'paypal_refund_id'])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'amount' => 'integer',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * The buyer.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The purchased product.
     *
     * Archived products stay reachable so buyers keep their purchase.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * The license issued for this order.
     *
     * @return HasOne<License, $this>
     */
    public function license(): HasOne
    {
        return $this->hasOne(License::class);
    }

    /**
     * Amount rendered in the order currency.
     */
    public function getAmountFormattedAttribute(): string
    {
        if ($this->amount === 0) {
            return 'Free';
        }

        return Number::currency(round($this->amount / 100, 2), $this->currency)
            ?: $this->currency.' '.number_format(round($this->amount / 100, 2), 2);
    }

    /**
     * Generate a human friendly order reference.
     */
    public static function generateReference(): string
    {
        do {
            $reference = 'ORD-'.Str::upper(Str::random(8));
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Human readable status name.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    /**
     * Whether the payment behind this order was refunded.
     */
    public function getRefundedAttribute(): bool
    {
        return $this->status === OrderStatus::Refunded;
    }

    /**
     * Public information about the customer that placed the order.
     *
     * @return array{id: int, name: string}
     */
    public function getCustomerAttribute(): array
    {
        return [
            'id' => $this->user->id,
            'name' => $this->user->name,
        ];
    }
}
