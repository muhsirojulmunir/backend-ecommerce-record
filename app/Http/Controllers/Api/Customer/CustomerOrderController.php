<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CheckoutRequest;
use App\Services\Customer\OrderService;
use App\Services\DuitkuService;
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

            // Siapkan data payment untuk dikembalikan ke frontend
            $paymentInfo = null;
            $method      = strtoupper($order->payment_method ?? '');
            $isManual    = in_array($method, ['COD', 'MANUAL_BCA'], true);

            if (! $isManual) {
                $paymentInfo = [
                    'payment_url' => $order->duitku_payment_url,
                    'va_number'   => $order->duitku_va_number,
                    'reference'   => $order->duitku_reference,
                    'qr_code'     => $order->duitku_qr_code ?? null,
                ];
            }

            $message = match ($method) {
                'COD'        => 'Pesanan berhasil dibuat. Pembayaran dilakukan saat barang tiba (COD).',
                'MANUAL_BCA' => 'Pesanan berhasil dibuat. Silakan transfer ke rekening BCA kami dan unggah bukti pembayaran.',
                default      => 'Pesanan berhasil dibuat. Silakan selesaikan pembayaran.',
            };

            return response()->json([
                'message'      => $message,
                'order'        => new OrderResource($order),
                'payment_info' => $paymentInfo,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function paymentMethods(Request $request, DuitkuService $duitku): JsonResponse
    {
        $amount  = (int) $request->query('amount', 10000);
        $result  = $duitku->getPaymentMethods($amount);

        return response()->json([
            'success' => $result['success'],
            'methods' => $result['data'],
        ]);
    }

    public function requestReturn(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'type'   => 'required|in:return,cancellation',
            'reason' => 'required|string',
        ]);

        try {
            $returnRequest = $this->orderService->requestReturnOrCancellation(
                $request->user()->id,
                $id,
                $request->all()
            );

            return response()->json([
                'message'        => 'Permintaan pengembalian/pembatalan berhasil diajukan.',
                'return_request' => $returnRequest,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
