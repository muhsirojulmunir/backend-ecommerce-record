<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getAllCategories($request->all());

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'uploaded_image' => 'nullable|image|max:1024',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $category = $this->categoryService->createCategory($request->all());

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan.',
            'category' => $category,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->categoryService->getCategoryById($id);

        return response()->json([
            'category' => $category,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'uploaded_image' => 'nullable|image|max:1024',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $category = $this->categoryService->updateCategory($id, $request->all());

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'category' => $category,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->categoryService->deleteCategory($id);

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
