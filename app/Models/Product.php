<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use Concerns\RecordsActivity;

    protected $activityLogName = 'produk';
    protected $activityModelLabel = 'produk';
    protected $activityLogAttributes = ['name', 'price', 'original_price', 'stock', 'status', 'is_featured', 'category_id'];

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'details',
        'price',
        'original_price',
        'stock',
        'image',
        'is_featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'details'      => 'array',
            'price'        => 'decimal:2',
            'original_price' => 'decimal:2',
            'is_featured'  => 'boolean',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function shipping(): HasOne
    {
        return $this->hasOne(ProductShipping::class);
    }

    public function discount(): HasOne
    {
        return $this->hasOne(Discount::class)->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            });
    }

    public function allDiscounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Semua ulasan, termasuk yang disembunyikan admin. */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
 * Hanya ulasan yang boleh dilihat pengunjung.
 */
    public function reviewsTampil(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('is_hidden', false);
    }

    // ─── Accessories ──────────────────────────────────────────────────────────

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image);
    }

    public function getDiscountedPriceAttribute(): float
    {
        if ($this->discount) {
            return round($this->price * (1 - $this->discount->discount_percentage / 100), 2);
        }
        return (float) $this->price;
    }
}
