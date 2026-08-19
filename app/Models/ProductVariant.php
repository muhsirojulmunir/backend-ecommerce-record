<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'color',
        'color_hex',
        'stock',
        'price_adjustment',
        'sku',
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment' => 'decimal:2',
            'stock'            => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class, 'product_variant_id');
    }

    public function activeDiscount(): HasOne
    {
        return $this->hasOne(Discount::class, 'product_variant_id')
            ->where('is_active', true)
            ->where(function ($q) { $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()); })
            ->where(function ($q) { $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()); });
    }

    public function getFinalPriceAttribute(): float
    {
        return (float) ($this->product->price + $this->price_adjustment);
    }
}
