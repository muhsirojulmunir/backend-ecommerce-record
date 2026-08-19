<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'material' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive,sold_out',
            
            // Shipping Info
            'weight_gram' => 'nullable|integer|min:1',
            'package_length' => 'nullable|numeric|min:0.1',
            'package_width' => 'nullable|numeric|min:0.1',
            'package_height' => 'nullable|numeric|min:0.1',
            'courier_providers' => 'nullable|array|min:1',
            'courier_providers.*' => 'string|in:jne,tiki,pos,jnt,sicepat,anteraja',
            
            // Images
            'uploaded_images' => 'nullable|array|max:5',
            'uploaded_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            
            // Variation Grid
            'variants' => 'nullable|array',
            'variants.*.color' => 'required|string|max:50',
            'variants.*.color_hex' => 'nullable|string|max:7',
            'variants.*.size' => 'required|string|max:20',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.price_adjustment' => 'required|numeric',
            'variants.*.sku' => 'nullable|string|max:100',
        ];
    }
}
