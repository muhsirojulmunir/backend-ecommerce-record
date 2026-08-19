<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Discount\StoreDiscountRequest;
use App\Http\Requests\Admin\Discount\StoreDiscountRequest as UpdateDiscountRequest; // can share or customize
use App\Services\Admin\DiscountService;
use App\Http\Resources\Admin\DiscountResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DiscountController extends Controller
{
    protected $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    public function index(Request $request): JsonResponse
    {
        $discounts = $this->discountService->getAllDiscounts($request->all());

        return response()->json([
            'discounts' => DiscountResource::collection($discounts),
        ]);
    }

    public function store(StoreDiscountRequest $request): JsonResponse
    {
        $discount = $this->discountService->setDiscount($request->validated());

        return response()->json([
            'message' => 'Diskon berhasil diterapkan pada produk.',
            'discount' => new DiscountResource($discount),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $discount = $this->discountService->getDiscountById($id);

        return response()->json([
            'discount' => new DiscountResource($discount),
        ]);
    }

    public function update(UpdateDiscountRequest $request, int $id): JsonResponse
    {
        $discount = $this->discountService->updateDiscount($id, $request->validated());

        return response()->json([
            'message' => 'Diskon berhasil diperbarui.',
            'discount' => new DiscountResource($discount),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->discountService->deleteDiscount($id);

        return response()->json([
            'message' => 'Diskon berhasil dihapus.',
        ]);
    }
}
