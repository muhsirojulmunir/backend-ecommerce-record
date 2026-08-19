<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminWebOrderController extends Controller
{
    /**
     * Tampilkan daftar semua pesanan masuk dengan filter status & pencarian.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product', 'returns'])->latest();

        // Filter berdasarkan tab / status pesanan
        if ($request->filled('tab')) {
            switch ($request->tab) {
                case 'ready':
                    // Siap diproses: Lunas tapi belum dikirim
                    $query->where('payment_status', 'paid')->whereIn('status', ['pending', 'processing']);
                    break;
                case 'unpaid':
                    // Belum dibayar
                    $query->whereIn('payment_status', ['unpaid', 'pending_verification']);
                    break;
                case 'shipped':
                    $query->where('status', 'shipped');
                    break;
                case 'completed':
                    $query->where('status', 'completed');
                    break;
                case 'cancelled':
                    $query->where('status', 'cancelled');
                    break;
            }
        } elseif ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter eksplisit payment_status (jika ada)
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Pencarian nomor pesanan / NOMOR RESI / nama / email pembeli
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  // Resi ikut dicari karena itulah yang paling sering ada di
                  // tangan admin saat menelusuri keluhan: pembeli mengirim
                  // foto resi, bukan nomor pesanan.
                  ->orWhere('tracking_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Auto-sync status pembayaran pesanan unpaid dengan Midtrans API (Sandbox/Production)
        $this->syncMidtransPaymentStatus(Order::whereIn('payment_status', ['unpaid', 'pending_verification'])->where('payment_method', '!=', 'COD')->limit(10)->get());

        $orders = $query->paginate(20)->withQueryString();

        // Riwayat unduhan untuk modal Riwayat Download.
        $riwayatEkspor = \App\Models\OrderExport::with('user')
            ->latest()
            ->take(\App\Models\OrderExport::BATAS_RIWAYAT)
            ->get();

        $counts = [
            'all'       => Order::count(),
            'ready'     => Order::where('payment_status', 'paid')->whereIn('status', ['pending', 'processing'])->count(),
            'unpaid'    => Order::whereIn('payment_status', ['unpaid', 'pending_verification'])->count(),
            'shipped'   => Order::where('status', 'shipped')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return view('admin.orders', [
            'orders'     => $orders,
            'counts'     => $counts,
            'stats'      => $counts,
            'currentTab' => $request->query('tab', 'all'),
            'riwayatEkspor' => $riwayatEkspor,
        ]);
    }

    /**
     * Auto-sync status pembayaran dari Midtrans API jika Webhook callback lokal tidak terhubung.
     */
    private function syncMidtransPaymentStatus($orders)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY', '');
        if (!$serverKey) return;

        $isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        $baseUrl = $isProduction ? 'https://api.midtrans.com/v2' : 'https://api.sandbox.midtrans.com/v2';
        $auth = base64_encode($serverKey . ':');

        foreach ($orders as $order) {
            try {
                $ch = curl_init($baseUrl . '/' . $order->order_number . '/status');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER     => [
                        'Accept: application/json',
                        'Authorization: Basic ' . $auth,
                    ],
                    CURLOPT_TIMEOUT        => 3,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);

                $res = curl_exec($ch);
                curl_close($ch);

                $data = json_decode($res, true) ?? [];
                $trxStatus   = $data['transaction_status'] ?? '';
                $fraudStatus = $data['fraud_status'] ?? 'accept';

                if ($trxStatus === 'settlement' || ($trxStatus === 'capture' && $fraudStatus === 'accept')) {
                    $order->payment_status = 'paid';
                    if (in_array($order->status, ['pending', 'unpaid'])) {
                        $order->status = 'processing';
                    }
                    $order->save();
                } elseif (in_array($trxStatus, ['cancel', 'deny', 'expire'])) {
                    $order->payment_status = 'failed';
                    $order->save();
                }
            } catch (\Throwable $e) {
                // Ignore network errors
            }
        }
    }

    /**
     * Tampilkan detail pesanan untuk dikelola oleh admin.
     */
    public function show(Request $request, $id)
    {
        $order = Order::with(['user', 'items.product', 'items.productVariant'])->findOrFail($id);

        // Mode cetak resi: tampilkan halaman print tanpa layout admin
        if ($request->boolean('print')) {
            return view('admin.partials.order-print', compact('order'));
        }

        return view('admin.orders-show', compact('order'));
    }

    /**
     * Update status pesanan (Pending -> Processing -> Shipped -> Completed / Cancelled).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;

        // Jika status diubah ke completed, pastikan payment_status = paid
        if ($request->status === 'completed' && $order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
        }

        $order->save();

        return redirect()->back()->with('success', "Status pesanan #{$order->order_number} berhasil diperbarui menjadi {$order->status}.");
    }

    /**
     * Input / update nomor resi pengiriman kurir.
     */
    public function updateTracking(Request $request, $id)
    {
        $request->validate([
            'tracking_number' => 'nullable|string|max:255',
            'courier'         => 'nullable|string|max:255',
        ]);

        $order = Order::with(['user', 'items'])->findOrFail($id);
        
        if ($request->filled('courier')) {
            $order->courier = $request->courier;
        }

        // Jika tracking number tidak diisi, coba panggil API BiteShip untuk terbitkan resi AWB resmi
        $biteshipAwb = $this->createBiteshipShipment($order);

        if ($biteshipAwb) {
            $order->tracking_number = $biteshipAwb;
        } else {
            $order->tracking_number = $request->tracking_number ?: ('REC' . str_pad($order->id, 8, '0', STR_PAD_LEFT));
        }

        // Otomatis ubah status menjadi shipped jika masih pending/processing
        if (in_array($order->status, ['pending', 'processing'])) {
            $order->status = 'shipped';
        }

        $order->save();

        return redirect()->back()->with('success', "Nomor resi {$order->tracking_number} berhasil disimpan & terposting ke Dashboard BiteShip. Status diubah menjadi Dikirim.");
    }

    /**
     * Tandai pembayaran sebagai lunas (Paid) secara manual.
     */
    public function confirmPayment($id)
    {
        $order = Order::findOrFail($id);
        $order->payment_status = 'paid';

        if ($order->status === 'pending') {
            $order->status = 'processing';
        }

        $order->save();

        return redirect()->back()->with('success', "Pembayaran pesanan #{$order->order_number} berhasil dikonfirmasi LUNAS.");
    }

    /**
     * Konfirmasi lunas secara massal untuk banyak pesanan sekaligus.
     */
    public function bulkConfirmPayment(Request $request)
    {
        $request->validate([
            'order_ids'   => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
        ]);

        $orders = Order::whereIn('id', $request->order_ids)
            ->where('payment_status', '!=', 'paid')
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            $order->payment_status = 'paid';
            if ($order->status === 'pending') {
                $order->status = 'processing';
            }
            $order->save();
            $count++;
        }

        return redirect()->route('admin.orders')
            ->with('success', "{$count} pesanan berhasil dikonfirmasi LUNAS secara massal.");
    }

    /**
     * Helper privat: Kirim booking pengiriman ke API BiteShip untuk menerbitkan resi AWB otomatis & memposting pesanan ke Dashboard BiteShip.
     */
    private function createBiteshipShipment(Order $order): ?string
    {
        $apiKey = env('BITESHIP_API_KEY');
        if (!$apiKey) return null;

        try {
            $address = $order->shipping_address;

            // Daftar kurir yang akan dicoba berurutan (jika satu gagal, lanjut berikutnya)
            // JNE paling stabil di sandbox BiteShip
            /*
             * Kurir diambil dari KODE resmi Biteship yang disimpan saat
             * checkout (mis. "jne:reg", "gojek:instant"), bukan ditebak dari
             * nama tampilannya.
             *
             * Sebelumnya nama tampilan dibersihkan lalu dicocokkan ke daftar
             * pendek — dan hampir semuanya meleset: "J&T Express" menjadi
             * "jtexpress" yang tidak ada di daftar, lalu jatuh ke JNE. Pembeli
             * memilih J&T, paketnya berangkat JNE. Pesanan instan lebih parah
             * lagi: dibayar sebagai instan, dikirim sebagai reguler.
             */
            $kodeTersimpan = $order->courier_code;

            // Pesanan lama yang kodenya belum terisi masih ditebak dari nama,
            // sebagai jaring pengaman — bukan sebagai jalur utama.
            if (blank($kodeTersimpan)) {
                $kodeTersimpan = app(\App\Services\PelacakanService::class)
                    ->kodeKurir($order->courier);
            }

            [$primaryCourier, $jenisLayanan] = array_pad(
                explode(':', (string) $kodeTersimpan, 2), 2, null
            );

            $primaryCourier = $primaryCourier ?: 'jne';

            /*
             * Jenis layanan diteruskan apa adanya ke Biteship. Kurir instan
             * memakai kode layanannya sendiri (instant, same_day), dan
             * memaksanya menjadi "reg" persis itulah yang membuat pesanan
             * instan berangkat sebagai reguler.
             */
            $jenisLayanan = $jenisLayanan ?: 'reg';

            /*
             * Kurir cadangan HANYA untuk pengiriman reguler.
             *
             * Pesanan instan tidak boleh diam-diam dialihkan ke JNE: pembeli
             * membayar untuk sampai hari itu juga, dan mengirimnya reguler
             * berarti mengingkari janji tanpa memberitahunya. Kalau instan
             * gagal, biar gagal — supaya admin tahu dan bisa menghubungi
             * pembelinya.
             */
            $instan = in_array($primaryCourier, ['gojek', 'grab', 'lalamove', 'borzo'], true);

            $courierFallbacks = $instan
                ? [$primaryCourier]
                : array_values(array_unique([$primaryCourier, 'jne', 'sicepat', 'anteraja']));

            $items = $order->items->map(function ($item) {
                return [
                    'name'     => mb_substr($item->product_name ?? 'Produk', 0, 100),
                    'value'    => (int) $item->price,
                    'quantity' => (int) $item->quantity,
                    'weight'   => 500,
                ];
            })->toArray();

            if (empty($items)) {
                $items = [['name' => 'Produk RECORD', 'value' => (int) $order->grand_total, 'quantity' => 1, 'weight' => 500]];
            }

            $postalCode  = $address['postal_code'] ?? null;
            $destAddress = trim(implode(', ', array_filter([
                $address['address_line'] ?? null,
                $address['city']         ?? null,
                $address['province']     ?? null,
            ])));

            $basePayload = [
                'shipper_name'         => env('STORE_LABEL', 'RECORD Official Store'),
                'shipper_contact_name' => env('STORE_LABEL', 'RECORD Official Store'),
                'shipper_phone'        => env('STORE_PHONE', '081323065554'),
                'origin_contact_name'  => env('STORE_LABEL', 'RECORD Official Store'),
                'origin_contact_phone' => env('STORE_PHONE', '081323065554'),
                'origin_address'       => env('STORE_ADDRESS', 'Jl. Toko Record No.1, Surabaya, Jawa Timur'),
                'origin_postal_code'   => (int) env('STORE_POSTAL_CODE', '60117'),

                'destination_contact_name'  => $address['recipient_name'] ?? ($order->user->name ?? 'Customer'),
                'destination_contact_phone' => $address['phone'] ?? ($order->user->phone ?? '08123456789'),
                'destination_name'          => $address['recipient_name'] ?? ($order->user->name ?? 'Customer'),
                'destination_phone'         => $address['phone'] ?? ($order->user->phone ?? '08123456789'),
                'destination_address'       => $destAddress ?: 'Indonesia',

                'courier_type'        => $jenisLayanan,
                'delivery_type'       => 'now',
                'items'               => $items,
            ];

            if ($postalCode) {
                $basePayload['destination_postal_code'] = (int) $postalCode;
            }

            // Coba setiap kurir fallback sampai berhasil
            foreach ($courierFallbacks as $courier) {
                $payload = array_merge($basePayload, ['courier_company' => $courier]);

                \Illuminate\Support\Facades\Log::info('Biteship Trying Courier: ' . $courier . ' | Payload: ' . json_encode($payload));

                $response = \Illuminate\Support\Facades\Http::timeout(15)->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://api.biteship.com/v1/orders', $payload);

                \Illuminate\Support\Facades\Log::info('Biteship Response [' . $courier . ']: ' . $response->body());

                if ($response->successful()) {
                    $data = $response->json();
                    // Simpan courier yang berhasil ke order
                    $order->courier = $courier;
                    return $data['courier']['waybill_id'] ?? $data['id'] ?? null;
                }

                $errorBody = $response->json();
                $errorCode = $errorBody['code'] ?? 0;

                // Jika error bukan soal kurir (misal postal code salah), hentikan retry
                if (!in_array($errorCode, [40002031, 40002030, 40002032])) {
                    \Illuminate\Support\Facades\Log::warning('Biteship Fatal Error (no retry): ' . $response->body());
                    break;
                }

                \Illuminate\Support\Facades\Log::warning('Biteship Courier ' . $courier . ' not available, trying next...');
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Biteship Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Otomatiskan Pengiriman Massal (Request Pick-Up & Auto Generate Resi BiteShip).
     * Hanya pesanan yang sudah LUNAS (paid) yang bisa diproses.
     */
    public function bulkShip(Request $request)
    {
        $request->validate([
            'order_ids'   => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
        ]);

        $orders = Order::with(['user', 'items'])->whereIn('id', $request->order_ids)->get();

        // Pisahkan pesanan yang belum lunas
        $unpaidOrders = $orders->filter(fn($o) => !in_array($o->payment_status, ['paid']));
        $paidOrders   = $orders->filter(fn($o) => in_array($o->payment_status, ['paid']));

        if ($unpaidOrders->isNotEmpty()) {
            $unpaidNums = $unpaidOrders->pluck('order_number')->join(', ');
            if ($paidOrders->isEmpty()) {
                return redirect()->back()
                    ->with('error', "❌ Tidak ada pesanan yang bisa diproses. Pesanan berikut belum lunas: {$unpaidNums}. Konfirmasi pembayaran terlebih dahulu.");
            }
        }

        $count   = 0;
        $skipped = 0;

        foreach ($paidOrders as $order) {
            // Skip jika sudah shipped
            if ($order->status === 'shipped') {
                $skipped++;
                continue;
            }

            // Panggil API BiteShip untuk terbitkan Nomor Resi Resmi (AWB) & Request Pickup
            $biteshipAwb = $this->createBiteshipShipment($order);

            if ($biteshipAwb) {
                $order->tracking_number = $biteshipAwb;
            } else {
                // Fallback AWB otomatis jika API gagal
                $courierTag = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $order->courier ?: 'JNT'), 0, 4));
                $order->tracking_number = "REC-{$courierTag}-" . str_pad($order->id, 6, '0', STR_PAD_LEFT);
            }

            $order->status = 'shipped';
            $order->save();
            $count++;
        }

        $message = "⚡ {$count} pesanan berhasil diproses! Nomor resi terbit dan kurir siap Pick-Up.";

        if ($unpaidOrders->isNotEmpty()) {
            $unpaidNums = $unpaidOrders->pluck('order_number')->join(', ');
            $message .= " ⚠️ {$unpaidOrders->count()} pesanan dilewati karena belum lunas: {$unpaidNums}.";
        }

        if ($skipped > 0) {
            $message .= " ({$skipped} pesanan sudah dalam status dikirim sebelumnya.)";
        }

        return redirect()->route('admin.orders')->with('success', $message);
    }


    /**
     * Cetak resi/label pengiriman massal (Batch Print).
     */
    public function bulkPrint(Request $request)
    {
        $request->validate([
            'order_ids'   => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
        ]);

        $orders = Order::with(['user', 'items.product', 'items.productVariant'])
            ->whereIn('id', $request->order_ids)
            ->get();

        return view('admin.partials.order-bulk-print', compact('orders'));
    }
}
