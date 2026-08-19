<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\ProductService;
use App\Http\Resources\Customer\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->getActiveProducts($request->all());

        return response()->json([
            'products' => ProductResource::collection($products),
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ]
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $product = $this->productService->getProductDetailBySlug($slug);

        return response()->json([
            'product' => new ProductResource($product),
        ]);
    }
}
