<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\UpdateOrderStatusRequest;
use App\Services\Admin\OrderService;
use App\Http\Resources\Admin\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->getAllOrders($request->all());

        // Check if returned data is returnRequest models instead of order models
        if ($request->get('tab') === 'returned') {
            return response()->json([
                'returns' => $orders, // standard pagination
            ]);
        }

        return response()->json([
            'orders' => OrderResource::collection($orders),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ]
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getOrderById($id);

        return response()->json([
            'order' => new OrderResource($order),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->updateOrderStatus($id, $request->validated());

        return response()->json([
            'message' => 'Status pesanan berhasil diperbarui.',
            'order' => new OrderResource($order),
        ]);
    }

    public function resolveReturn(Request $request, int $returnId): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        $returnRequest = $this->orderService->handleReturnRequest($returnId, $request->all());

        return response()->json([
            'message' => 'Permintaan pengembalian/pembatalan berhasil diproses.',
            'return_request' => $returnRequest,
        ]);
    }
}
