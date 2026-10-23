<?php

namespace App\Models;

use App\Enums\AppPlatform;
use App\Enums\ProductStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use RuntimeException;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string $tagline
 * @property string $description
 * @property AppPlatform $platform
 * @property ProductStatus $status
 * @property int $price
 * @property string $currency
 * @property string|null $version
 * @property string|null $requirements
 * @property string|null $cover_image
 * @property string|null $demo_url
 * @property string|null $download_url
 * @property string|null $repository_url
 * @property int $views
 * @property int $sales
 * @property Carbon|null $published_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $cover_image_url
 * @property-read string $price_formatted
 * @property-read bool $is_free
 */
#[Fillable([
    'name',
    'slug',
    'tagline',
    'description',
    'platform',
    'status',
    'price',
    'currency',
    'version',
    'requirements',
    'cover_image',
    'demo_url',
    'download_url',
    'repository_url',
    'published_at',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Register the model events that protect the sales history.
     */
    protected static function booted(): void
    {
        static::forceDeleting(function (self $product): void {
            if ($product->orders()->exists()) {
                throw new RuntimeException(
                    'An app with sales cannot be permanently deleted. Archive it instead.'
                );
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'platform' => AppPlatform::class,
            'status' => ProductStatus::class,
            'price' => 'integer',
            'published_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * The developer that owns this product.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<ProductScreenshot, $this>
     */
    public function screenshots(): HasMany
    {
        return $this->hasMany(ProductScreenshot::class)->orderBy('position');
    }

    /**
     * @return HasMany<ProductFeature, $this>
     */
    public function features(): HasMany
    {
        return $this->hasMany(ProductFeature::class)->orderBy('position');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('tags.name');
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<License, $this>
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    /**
     * @param  Builder<Product>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ProductStatus::Published);
    }

    /**
     * @param  Builder<Product>  $query
     */
    public function scopeOwnedBy(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    /**
     * Publicly reachable URL of the cover image.
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        if (blank($this->cover_image)) {
            return null;
        }

        return Storage::disk('public')->url($this->cover_image);
    }

    /**
     * Price rendered in the product currency.
     */
    public function getPriceFormattedAttribute(): string
    {
        if ($this->is_free) {
            return 'Free';
        }

        return Number::currency(round($this->price / 100, 2), $this->currency)
            ?: $this->currency.' '.number_format(round($this->price / 100, 2), 2);
    }

    /**
     * Whether the product can be purchased without paying.
     */
    public function getIsFreeAttribute(): bool
    {
        return $this->price === 0;
    }

    /**
     * Human readable platform name.
     */
    public function getPlatformLabelAttribute(): string
    {
        return $this->platform->label();
    }

    /**
     * Icon key for the platform.
     */
    public function getPlatformIconAttribute(): string
    {
        return $this->platform->icon();
    }

    /**
     * Human readable status name.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    /**
     * Public information about the developer that built this app.
     *
     * The full owner record is never shared with buyers.
     *
     * @return array{id: int, name: string}
     */
    public function getSellerAttribute(): array
    {
        return [
            'id' => $this->user->id,
            'name' => $this->user->name,
        ];
    }
}
