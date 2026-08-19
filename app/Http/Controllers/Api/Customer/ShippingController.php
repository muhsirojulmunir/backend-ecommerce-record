<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShippingController extends Controller
{
    protected $shippingService;

    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    public function provinces(): JsonResponse
    {
        $provinces = $this->shippingService->getProvinces();

        return response()->json([
            'provinces' => $provinces,
        ]);
    }

    public function cities(Request $request): JsonResponse
    {
        $provinceId = $request->get('province_id');
        $cities = $this->shippingService->getCities($provinceId);

        return response()->json([
            'cities' => $cities,
        ]);
    }

    public function calculateCost(Request $request): JsonResponse
    {
        $request->validate([
            'destination_city_id' => 'required|integer',
            'weight_gram' => 'required|integer|min:1',
            'courier' => 'required|string|in:jne,pos,tiki,jnt,sicepat,anteraja',
        ]);

        $costs = $this->shippingService->calculateCost($request->all());

        return response()->json([
            'costs' => $costs,
        ]);
    }
}
