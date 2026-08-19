<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_info',
        'quantity',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price'    => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Ulasan untuk barang ini. Satu baris pesanan hanya boleh punya satu,
     * dijaga oleh kunci unik di basis data.
     */
    public function review(): HasOne
    {
        return $this->hasOne(ProductReview::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->price * $this->quantity);
    }
}
