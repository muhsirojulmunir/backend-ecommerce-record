<?php

namespace App\Services\Admin;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductImage;

class ProductService
{
    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAllProducts(array $filters = [])
    {
        return $this->productRepository->paginate(15, $filters);
    }

    public function getProductById(int $id)
    {
        return $this->productRepository->findById($id);
    }

    public function createProduct(array $data)
    {
        // 1. Generate slug
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);

        // 2. Image — the controller already stored files and set $data['image'] and $data['images']
        if (!isset($data['image'])) {
            $data['image'] = 'products/default.png';
        }

        // 3. Format details/material
        $data['details'] = [
            'material' => $data['material'] ?? '',
        ];

        // 4. Format shipping
        $data['shipping'] = [
            'weight_gram'      => $data['weight_gram']      ?? 0,
            'package_length'   => $data['package_length']   ?? 0,
            'package_width'    => $data['package_width']    ?? 0,
            'package_height'   => $data['package_height']   ?? 0,
            'courier_providers'=> $data['courier_providers'] ?? ['jne', 'tiki', 'pos'],
        ];

        // 5. Process variant grid
        $data['variants'] = $this->formatVariants($data['variants'] ?? []);

        return $this->productRepository->create($data);
    }

    public function updateProduct(int $id, array $data)
    {
        $product = $this->productRepository->findById($id);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
        }

        // Images — controller already handled slot-based upload & deletion;
        // $data['image'] and $data['images'] are pre-built.

        if (isset($data['material'])) {
            $data['details'] = [
                'material' => $data['material'],
            ];
        }

        // Shipping
        if (isset($data['weight_gram']) || isset($data['package_length'])) {
            $data['shipping'] = [
                'weight_gram'       => $data['weight_gram']       ?? optional($product->shipping)->weight_gram    ?? 0,
                'package_length'    => $data['package_length']    ?? optional($product->shipping)->package_length ?? 0,
                'package_width'     => $data['package_width']     ?? optional($product->shipping)->package_width  ?? 0,
                'package_height'    => $data['package_height']    ?? optional($product->shipping)->package_height ?? 0,
                'courier_providers' => $data['courier_providers'] ?? optional($product->shipping)->courier_providers ?? ['jne', 'tiki', 'pos'],
            ];
        }

        // Variants
        if (isset($data['variants'])) {
            $data['variants'] = $this->formatVariants($data['variants']);
        }

        return $this->productRepository->update($id, $data);
    }

    public function deleteProduct(int $id): bool
    {
        $product = $this->productRepository->findById($id);

        // Delete images from storage
        foreach ($product->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }

        return $this->productRepository->delete($id);
    }

    /**
     * Format variants grid to match DB schema attributes.
     */
    protected function formatVariants(array $rawVariants): array
    {
        $formatted = [];
        foreach ($rawVariants as $item) {
            $formatted[] = [
                'color' => $item['color'] ?? 'Default',
                'color_hex' => $item['color_hex'] ?? null,
                'size' => $item['size'] ?? 'All Size',
                'stock' => $item['stock'] ?? 0,
                'price_adjustment' => $item['price_adjustment'] ?? 0, // adjusts base price
                'sku' => $item['sku'] ?? (Str::random(10)),
            ];
        }
        return $formatted;
    }
}
