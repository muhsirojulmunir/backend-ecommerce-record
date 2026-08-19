<?php

namespace App\Repositories;

use App\Models\Discount;
use App\Repositories\Interfaces\DiscountRepositoryInterface;

class DiscountRepository implements DiscountRepositoryInterface
{
    public function all(array $filters = [])
    {
        $query = Discount::with('product');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function findById(int $id)
    {
        return Discount::with('product')->findOrFail($id);
    }

    public function findByProductId(int $productId)
    {
        return Discount::with('product')->where('product_id', $productId)->first();
    }

    public function create(array $data)
    {
        return Discount::create($data);
    }

    public function update(int $id, array $data)
    {
        $discount = Discount::findOrFail($id);
        $discount->update($data);
        return $discount;
    }

    public function delete(int $id): bool
    {
        $discount = Discount::findOrFail($id);
        return $discount->delete();
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = Discount::with('product');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }
}
