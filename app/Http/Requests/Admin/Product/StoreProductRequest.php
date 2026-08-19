<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'material' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive,sold_out',
            
            // Shipping Info
            'weight_gram' => 'required|integer|min:1',
            'package_length' => 'required|numeric|min:0.1',
            'package_width' => 'required|numeric|min:0.1',
            'package_height' => 'required|numeric|min:0.1',
            'courier_providers' => 'required|array|min:1',
            'courier_providers.*' => 'string|in:jne,tiki,pos,jnt,sicepat,anteraja',
            
            // Images
            'uploaded_images' => 'required|array|min:1|max:5',
            'uploaded_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048', // max 2MB
            
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

    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'category_id.exists' => 'Kategori produk tidak valid.',
            'price.required' => 'Harga produk wajib diisi.',
            'stock.required' => 'Stok produk wajib diisi.',
            'weight_gram.required' => 'Berat produk (gram) wajib diisi.',
            'package_length.required' => 'Panjang paket (cm) wajib diisi.',
            'package_width.required' => 'Lebar paket (cm) wajib diisi.',
            'package_height.required' => 'Tinggi paket (cm) wajib diisi.',
            'uploaded_images.required' => 'Foto produk wajib diunggah minimal 1 foto.',
            'uploaded_images.max' => 'Foto produk maksimal 5 foto.',
        ];
    }
}
