<?php

namespace App\Services;

use App\Models\OrderReturn;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BiteshipReturnService
{
    /**
 * Otomatis booking pengiriman balik (Retur) via API Biteship.
 *
 * @param OrderReturn $pengajuan
 * @return array|null Array [tracking_number, courier, biteship_order_id] atau null jika gagal.
 */
    public function createReturnShipment(OrderReturn $pengajuan): ?array
    {
        $apiKey = env('BITESHIP_API_KEY');
        if (!$apiKey) {
            Log::warning('Biteship API Key belum diatur di .env');
            return null;
        }

        $order = $pengajuan->order;
        if (!$order) return null;

        try {
            $address = $order->shipping_address;

            // Detail Alamat Pembeli (Origin Retur)
            $customerName  = $address['recipient_name'] ?? ($pengajuan->user->name ?? 'Pembeli RECORD');
            $customerPhone = $address['phone'] ?? ($pengajuan->user->phone ?? '08123456789');
            $postalCode    = $address['postal_code'] ?? null;
            $destAddress   = trim(implode(', ', array_filter([
                $address['address_line'] ?? null,
                $address['city']         ?? null,
                $address['province']     ?? null,
            ])));

            // Barang yang dikembalikan
            $items = $order->items->map(function ($item) {
                return [
                    'name'     => 'RETUR: ' . mb_substr($item->product_name ?? 'Produk RECORD', 0, 90),
                    'value'    => (int) $item->price,
                    'quantity' => (int) $item->quantity,
                    'weight'   => 500,
                ];
            })->toArray();

            if (empty($items)) {
                $items = [[
                    'name'     => 'RETUR: Pesanan #' . $order->order_number,
                    'value'    => (int) $order->grand_total,
                    'quantity' => 1,
                    'weight'   => 500
                ]];
            }

            // Kurir penjemputan retur yang akan dicoba berurutan (JNE -> SiCepat -> AnterAja)
            $couriers = ['jne', 'sicepat', 'anteraja'];

            $basePayload = [
                // Shipper & Origin = Alamat Pembeli
                'shipper_name'         => $customerName,
                'shipper_contact_name' => $customerName,
                'shipper_phone'        => $customerPhone,
                'origin_contact_name'  => $customerName,
                'origin_contact_phone' => $customerPhone,
                'origin_address'       => $destAddress ?: 'Indonesia',

                // Destination = Alamat Gudang Toko di Surabaya
                'destination_contact_name'  => env('STORE_LABEL', 'RECORD Official Store - Retur Center'),
                'destination_contact_phone' => env('STORE_PHONE', '081323065554'),
                'destination_name'          => env('STORE_LABEL', 'RECORD Official Store - Retur Center'),
                'destination_phone'         => env('STORE_PHONE', '081323065554'),
                'destination_address'       => env('STORE_ADDRESS', 'Jl. Toko Record No.1, Surabaya, Jawa Timur'),
                'destination_postal_code'   => (int) env('STORE_POSTAL_CODE', '60117'),

                'courier_type'        => 'reg',
                'delivery_type'       => 'now',
                'items'               => $items,
                'notes'               => 'Pengembalian barang / retur pesanan #' . $order->order_number,
            ];

            if ($postalCode) {
                $basePayload['origin_postal_code'] = (int) $postalCode;
            }

            if (!empty($address['latitude']) && !empty($address['longitude'])) {
                $basePayload['origin_latitude']  = (float) $address['latitude'];
                $basePayload['origin_longitude'] = (float) $address['longitude'];
            }

            // Tambahkan koordinat gudang toko penerima jika ada
            if (env('STORE_LATITUDE') && env('STORE_LONGITUDE')) {
                $basePayload['destination_latitude']  = (float) env('STORE_LATITUDE', -7.2575);
                $basePayload['destination_longitude'] = (float) env('STORE_LONGITUDE', 112.7521);
            }

            $endpoint = rtrim(env('BITESHIP_API_URL', 'https://api.biteship.com/v1'), '/') . '/orders';

            foreach ($couriers as $courierCode) {
                $payload = array_merge($basePayload, [
                    'courier_company' => $courierCode,
                ]);

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->post($endpoint, $payload);

                $json = $response->json();

                if ($response->successful() && !empty($json['success']) && $json['success']) {
                    $waybillId = $json['courier']['waybill_id']
                        ?? $json['courier']['tracking_id']
                        ?? $json['id']
                        ?? null;

                    $courierName = strtoupper($json['courier']['company'] ?? $courierCode);
                    $biteshipOrderId = $json['id'] ?? null;

                    Log::info("Retur Biteship Berhasil ($courierCode):", [
                        'order_number' => $order->order_number,
                        'waybill'      => $waybillId,
                        'biteship_id'  => $biteshipOrderId,
                    ]);

                    return [
                        'tracking_number'   => $waybillId ?: ('RTR-BITESHIP-' . time()),
                        'courier'           => $courierName,
                        'biteship_order_id' => $biteshipOrderId,
                    ];
                }

                Log::warning("Coba kurir retur $courierCode gagal:", [
                    'status' => $response->status(),
                    'error'  => $json['error'] ?? $json['message'] ?? 'Unknown error',
                ]);
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('Exception saat booking retur Biteship:', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
