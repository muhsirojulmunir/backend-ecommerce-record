<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CheckoutRequest;
use App\Services\Customer\OrderService;
use App\Http\Resources\Customer\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerOrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->getCustomerOrders($request->user()->id);

        return response()->json([
            'orders' => OrderResource::collection($orders),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getOrderDetail($request->user()->id, $id);
            return response()->json([
                'order' => new OrderResource($order),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->checkout($request->user()->id, $request->validated());

            return response()->json([
                'message' => 'Checkout berhasil. Silakan lakukan pembayaran.',
                'order' => new OrderResource($order),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function requestReturn(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:return,cancellation',
            'reason' => 'required|string',
        ]);

        try {
            $returnRequest = $this->orderService->requestReturnOrCancellation(
                $request->user()->id,
                $id,
                $request->all()
            );

            return response()->json([
                'message' => 'Permintaan pengembalian/pembatalan berhasil diajukan.',
                'return_request' => $returnRequest,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
