<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerVoucherController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $voucher = Voucher::where('code', $code)->first();

        if (! $voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Kode voucher tidak ditemukan.',
            ], 404);
        }

        if ($voucher->is_used) {
            return response()->json([
                'success' => false,
                'message' => 'Kode voucher sudah pernah digunakan.',
            ], 422);
        }

        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Kode voucher sudah kadaluarsa pada ' . $voucher->expires_at->format('d/m/Y H:i') . '.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'code'             => $voucher->code,
                'amount'           => $voucher->amount,
                'formatted_amount' => $voucher->formatted_amount,
                'expires_at'       => $voucher->expires_at?->format('Y-m-d H:i:s'),
            ],
            'message' => 'Voucher valid.',
        ]);
    }
}
