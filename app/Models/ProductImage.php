<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'product_id',
    'product_variant_id',
    'path',
    'disk',
    'alt',
    'is_primary',
    'sort_order',
])]
class ProductImage extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (ProductImage $image): void {
            if (blank($image->path)) {
                return;
            }

            Storage::disk($image->disk ?: 'public')->delete($image->path);
        });
    }
}
