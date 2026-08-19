<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function all(array $filters = [])
    {
        $query = Category::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('sort_order')->orderBy('id', 'desc')->get();
    }

    public function findById(int $id)
    {
        return Category::findOrFail($id);
    }

    public function findBySlug(string $slug)
    {
        return Category::where('slug', $slug)->firstOrFail();
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update(int $id, array $data)
    {
        $category = Category::findOrFail($id);
        $category->update($data);
        return $category;
    }

    public function delete(int $id): bool
    {
        $category = Category::findOrFail($id);
        return $category->delete();
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = Category::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('sort_order')->orderBy('id', 'desc')->paginate($perPage);
    }
}
