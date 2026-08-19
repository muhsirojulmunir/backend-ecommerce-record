<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
    use Concerns\RecordsActivity;

    protected $activityLogName = 'banner';
    protected $activityModelLabel = 'banner';
    protected $activityLogAttributes = ['title', 'position', 'is_active', 'sort_order', 'link', 'image'];

    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'link',
        'position',
        'is_active',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
            'starts_at'  => 'datetime',
            'ends_at'    => 'datetime',
        ];
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image);
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
