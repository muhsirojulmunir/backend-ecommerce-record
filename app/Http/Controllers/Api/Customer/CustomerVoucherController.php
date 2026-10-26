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
            'code'      => 'required|string|max:50',
            'device_id' => 'nullable|string|max:64',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $voucher = Voucher::where('code', $code)->first();

        if (! $voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Kode voucher tidak ditemukan.',
            ], 404);
        }

        $userId   = auth('sanctum')->id() ?? auth()->id() ?? $request->user()?->id;
        $deviceId = $request->input('device_id') ?? $request->header('X-Device-Id');

        $validation = $voucher->validateForUsage($userId, $deviceId);

        if (! $validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'code'             => $voucher->code,
                'amount'           => $voucher->amount,
                'formatted_amount' => $voucher->formatted_amount,
                'type'             => $voucher->type ?? 'single',
                'expires_at'       => $voucher->expires_at?->format('Y-m-d H:i:s'),
            ],
            'message' => 'Voucher valid.',
        ]);
    }
}
