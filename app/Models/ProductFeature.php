<?php

namespace App\Models;

use Database\Factories\ProductFeatureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property string $title
 * @property string|null $description
 * @property int $position
 */
#[Fillable(['product_id', 'title', 'description', 'position'])]
class ProductFeature extends Model
{
    /** @use HasFactory<ProductFeatureFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
