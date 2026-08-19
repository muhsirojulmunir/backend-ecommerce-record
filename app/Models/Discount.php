<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discount extends Model
{
    use Concerns\RecordsActivity;

    protected $activityLogName = 'diskon';
    protected $activityModelLabel = 'diskon';
    protected $activityLogAttributes = ['product_id', 'discount_percentage', 'is_active', 'starts_at', 'ends_at'];

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'discount_percentage',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
            'is_active'           => 'boolean',
            'starts_at'           => 'datetime',
            'ends_at'             => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }
}
