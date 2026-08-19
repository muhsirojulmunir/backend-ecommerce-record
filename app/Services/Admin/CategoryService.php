<?php

namespace App\Services\Admin;

use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Str;

class CategoryService
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllCategories(array $filters = [])
    {
        return $this->categoryRepository->all($filters);
    }

    public function getCategoryById(int $id)
    {
        return $this->categoryRepository->findById($id);
    }

    public function createCategory(array $data)
    {
        $data['slug'] = Str::slug($data['name']);

        if (isset($data['uploaded_image'])) {
            $data['image'] = $data['uploaded_image']->store('categories', 'public');
        }

        return $this->categoryRepository->create($data);
    }

    public function updateCategory(int $id, array $data)
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if (isset($data['uploaded_image'])) {
            $data['image'] = $data['uploaded_image']->store('categories', 'public');
        }

        return $this->categoryRepository->update($id, $data);
    }

    public function deleteCategory(int $id): bool
    {
        return $this->categoryRepository->delete($id);
    }
}
