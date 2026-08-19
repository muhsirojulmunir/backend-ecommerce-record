<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductShipping extends Model
{
    protected $fillable = [
        'product_id',
        'weight_gram',
        'package_length',
        'package_width',
        'package_height',
        'courier_providers',
    ];

    protected function casts(): array
    {
        return [
            'courier_providers' => 'array',
            'weight_gram'       => 'integer',
            'package_length'    => 'decimal:2',
            'package_width'     => 'decimal:2',
            'package_height'    => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
