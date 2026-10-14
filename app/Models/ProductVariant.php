<?php

namespace App\Models;

use App\Enums\StockPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'product_id',
    'sku',
    'base_price',
    'stock_quantity',
    'stock_policy',
    'weight_grams',
    'length_mm',
    'width_mm',
    'height_mm',
    'is_active',
    'sort_order',
])]
class ProductVariant extends Model
{
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stock_policy' => StockPolicy::class,
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function optionValues(): BelongsToMany
    {
        return $this->belongsToMany(ProductOptionValue::class, 'variant_option_values')
            ->withPivot(['surcharge', 'is_active'])
            ->withTimestamps();
    }

    public function variantOptionValues(): HasMany
    {
        return $this->hasMany(VariantOptionValue::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
