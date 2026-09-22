<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DuitkuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * DuitkuCallbackController
 *
 * Menerima webhook/callback dari server Duitku setelah pembayaran.
 * Endpoint ini TIDAK pakai middleware auth karena dipanggil oleh Duitku.
 *
 * URL: POST /duitku/callback
 */
class DuitkuCallbackController extends Controller
{
    public function handle(Request $request, DuitkuService $duitku)
    {
        $data = $request->all();

        Log::info('Duitku callback received', $data);

        // 1. Verifikasi signature dari Duitku
        if (! $duitku->verifyCallbackSignature($data)) {
            Log::warning('Duitku callback: INVALID SIGNATURE', $data);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $merchantOrderId = $data['merchantOrderId'] ?? null;
        $resultCode      = $data['resultCode'] ?? null;
        $reference       = $data['reference'] ?? null;
        $amount          = $data['amount'] ?? null;

        if (! $merchantOrderId) {
            return response()->json(['message' => 'merchantOrderId missing'], 400);
        }

        // 2. Cari order berdasarkan order_number
        $order = Order::where('order_number', $merchantOrderId)->first();

        if (! $order) {
            Log::warning("Duitku callback: order tidak ditemukan [{$merchantOrderId}]");
            return response()->json(['message' => 'Order not found'], 404);
        }

        // 3. Update status berdasarkan resultCode Duitku
        if ($duitku->isPaymentSuccessful($resultCode)) {
            $order->update([
                'payment_status'   => 'paid',
                'status'           => 'processing',
                'duitku_reference' => $reference,
            ]);

            Log::info("Duitku callback: PAID — order [{$merchantOrderId}]");

        } elseif ($duitku->isPaymentFailed($resultCode)) {
            $order->update([
                'payment_status' => 'failed',
                'status'         => 'cancelled',
            ]);

            Log::info("Duitku callback: FAILED — order [{$merchantOrderId}]");

        } else {
            // resultCode '01' = pending, atau kode lain
            Log::info("Duitku callback: PENDING (resultCode={$resultCode}) — order [{$merchantOrderId}]");
        }

        // Duitku mengharapkan response 200 OK
        return response()->json(['message' => 'OK']);
    }
}
