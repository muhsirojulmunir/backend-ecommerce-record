<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductShipping;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    public function all(array $filters = [])
    {
        $query = Product::with(['category', 'images', 'variants', 'shipping', 'discount']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (filled($filters['search'] ?? null)) {
            $kata = trim($filters['search']);

            /*
             * Dicari pada nama produk ATAU kode SKU variannya, supaya admin
             * bisa langsung mengetik SKU yang tertera di gudang tanpa perlu
             * mengingat nama produknya.
             *
             * SKU melekat pada varian, bukan pada produk — satu produk punya
             * banyak SKU, satu per ukuran dan warna. Karena itu lewat
             * whereHas, dan yang dikembalikan tetap produknya beserta seluruh
             * variannya.
             *
             * Seluruhnya dibungkus satu where() supaya tidak bertabrakan
             * dengan saringan status dan kategori di atas — tanpa pembungkus
             * itu, "atau" di sini akan membatalkan keduanya.
             */
            $query->where(function ($q) use ($kata) {
                $q->where('name', 'like', "%{$kata}%")
                  ->orWhereHas('variants', fn ($v) => $v->where('sku', 'like', "%{$kata}%"));
            });
        }

        if (isset($filters['featured'])) {
            $query->where('is_featured', $filters['featured']);
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function findById(int $id)
    {
        return Product::with(['category', 'images', 'variants', 'shipping', 'discount'])->findOrFail($id);
    }

    public function findBySlug(string $slug)
    {
        return Product::with(['category', 'images', 'variants', 'shipping', 'discount'])->where('slug', $slug)->firstOrFail();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Create Base Product
            $product = Product::create($data);

            // 2. Create Shipping Info if provided
            if (isset($data['shipping'])) {
                $product->shipping()->create($data['shipping']);
            }

            // 3. Create Variants if provided
            if (isset($data['variants']) && is_array($data['variants'])) {
                foreach ($data['variants'] as $variant) {
                    $product->variants()->create($variant);
                }
            }

            // 4. Create Product Images if provided
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $index => $imagePath) {
                    $product->images()->create([
                        'image_path' => $imagePath,
                        'sort_order' => $index,
                    ]);
                }
            }

            return $product->load(['category', 'images', 'variants', 'shipping']);
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $product = Product::findOrFail($id);
            $product->update($data);

            // Update Shipping Info
            if (isset($data['shipping'])) {
                $product->shipping()->updateOrCreate([], $data['shipping']);
            }

            // Update Variants (Sync variants)
            if (isset($data['variants']) && is_array($data['variants'])) {
                // Remove variants not in the update request if you want sync, or just delete and recreate
                $product->variants()->delete();
                foreach ($data['variants'] as $variant) {
                    $product->variants()->create($variant);
                }
            }

            // Sync Images (if provided).
            // Hanya foto galeri umum yang disegarkan; foto khusus warna
            // (kolom color terisi) dikelola terpisah oleh controller.
            if (isset($data['images']) && is_array($data['images'])) {
                $product->images()->whereNull('color')->delete();
                foreach ($data['images'] as $index => $imagePath) {
                    $product->images()->create([
                        'image_path' => $imagePath,
                        'sort_order' => $index,
                    ]);
                }
            }

            return $product->load(['category', 'images', 'variants', 'shipping', 'discount']);
        });
    }

    public function delete(int $id): bool
    {
        $product = Product::findOrFail($id);
        return $product->delete();
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = Product::with(['category', 'images', 'variants', 'shipping', 'discount']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (filled($filters['search'] ?? null)) {
            $kata = trim($filters['search']);

            /*
             * Dicari pada nama produk ATAU kode SKU variannya, supaya admin
             * bisa langsung mengetik SKU yang tertera di gudang tanpa perlu
             * mengingat nama produknya.
             *
             * SKU melekat pada varian, bukan pada produk — satu produk punya
             * banyak SKU, satu per ukuran dan warna. Karena itu lewat
             * whereHas, dan yang dikembalikan tetap produknya beserta seluruh
             * variannya.
             *
             * Seluruhnya dibungkus satu where() supaya tidak bertabrakan
             * dengan saringan status dan kategori di atas — tanpa pembungkus
             * itu, "atau" di sini akan membatalkan keduanya.
             */
            $query->where(function ($q) use ($kata) {
                $q->where('name', 'like', "%{$kata}%")
                  ->orWhereHas('variants', fn ($v) => $v->where('sku', 'like', "%{$kata}%"));
            });
        }

        if (isset($filters['featured'])) {
            $query->where('is_featured', $filters['featured']);
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }
}
