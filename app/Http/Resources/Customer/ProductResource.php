<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'material' => $this->details['material'] ?? null,
            'price' => (float) $this->price,
            'discounted_price' => $this->discounted_price,
            'stock' => $this->stock,
            'cover_image' => $this->image_url,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'images' => $this->images->map(function ($img) {
                return $img->image_url;
            }),
            'variants' => $this->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'color' => $variant->color,
                    'color_hex' => $variant->color_hex,
                    'size' => $variant->size,
                    'stock' => $variant->stock,
                    'price_adjustment' => (float) $variant->price_adjustment,
                    'final_price' => $variant->final_price,
                ];
            }),
            'shipping' => [
                'weight_gram' => $this->shipping?->weight_gram,
                'package_length' => (float) $this->shipping?->package_length,
                'package_width' => (float) $this->shipping?->package_width,
                'package_height' => (float) $this->shipping?->package_height,
            ],
            'discount' => $this->discount ? [
                'percentage' => (float) $this->discount->discount_percentage,
                'starts_at' => $this->discount->starts_at?->toDateTimeString(),
                'ends_at' => $this->discount->ends_at?->toDateTimeString(),
            ] : null,
        ];
    }
}
