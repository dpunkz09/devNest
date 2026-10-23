<?php

namespace App\Models;

use Database\Factories\LicenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $user_id
 * @property string $key
 * @property int $downloads
 * @property Carbon|null $activated_at
 * @property Carbon|null $revoked_at
 */
#[Fillable(['order_id', 'product_id', 'user_id', 'key', 'activated_at', 'revoked_at'])]
class License extends Model
{
    /** @use HasFactory<LicenseFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'downloads' => 'integer',
            'activated_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The licensed app.
     *
     * Archived apps stay reachable so the license keeps working.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a unique, human friendly license key.
     */
    public static function generateKey(): string
    {
        do {
            $key = implode('-', array_map(
                fn (): string => Str::upper(Str::random(5)),
                range(1, 3),
            ));
        } while (self::where('key', $key)->exists());

        return $key;
    }

    /**
     * Whether the license was taken back after a refund.
     */
    public function getRevokedAttribute(): bool
    {
        return $this->revoked_at !== null;
    }
}
