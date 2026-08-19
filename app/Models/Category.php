<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use Concerns\RecordsActivity;

    protected $activityLogName = 'kategori';
    protected $activityModelLabel = 'kategori';
    protected $activityLogAttributes = ['name', 'slug', 'is_active', 'sort_order'];

    protected $fillable = [
        'name',
        'slug',
        'image',
        'image_position_x',
        'image_position_y',
        'image_zoom',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'        => 'boolean',
            'sort_order'       => 'integer',
            'image_position_x' => 'integer',
            'image_position_y' => 'integer',
            'image_zoom'       => 'float',
        ];
    }

    /**
     * Gaya CSS untuk menampilkan gambar sesuai posisi & perbesaran
     * yang diatur admin. Dipakai sama persis di panel admin maupun
     * di halaman toko supaya hasilnya identik.
     */
    public function getImageStyleAttribute(): string
    {
        $x    = $this->image_position_x ?? 50;
        $y    = $this->image_position_y ?? 50;
        $zoom = $this->image_zoom ?: 1;

        return "object-fit:cover;object-position:{$x}% {$y}%;"
            . "transform:scale({$zoom});transform-origin:{$x}% {$y}%;";
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
