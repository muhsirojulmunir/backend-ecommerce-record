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

        /*
         * Resi yang diketik admin sendiri dihormati apa adanya.
         *
         * Kalau admin sudah memegang resi — misalnya paket diantar sendiri atau
         * dibuat manual di dasbor Biteship — tidak ada gunanya memanggil API
         * lagi. Panggilan itu justru menciptakan order kedua di Biteship dan
         * memotong saldo untuk pengiriman yang sama.
         */
        if ($request->filled('tracking_number')) {
            $order->tracking_number = trim($request->tracking_number);

            if (in_array($order->status, ['pending', 'processing'])) {
                $order->status = 'shipped';
            }

            $order->save();

            return redirect()->back()->with('success',
                "Nomor resi {$order->tracking_number} tersimpan. Status diubah menjadi Dikirim.");
        }

        // Resi kosong: minta Biteship menerbitkannya sekaligus menjemput paket.
        $hasil = $this->createBiteshipShipment($order);

        /*
         * Gagal tidak lagi ditambal dengan resi buatan sendiri. Resi palsu
         * membuat pesanan tampak terkirim padahal tidak ada kurir yang dipanggil,
         * dan pembeli menunggu paket yang tidak pernah dijemput.
         */
        if (! $hasil['berhasil']) {
            return redirect()->back()->with('error',
                'Resi gagal diterbitkan, jadi status pesanan tidak diubah. '
                . $hasil['alasan']
                . ($hasil['soal_saldo'] ? ' Isi saldo Biteship lalu ulangi.' : ''));
        }

        $order->tracking_number = $hasil['resi'];

        // Otomatis ubah status menjadi shipped jika masih pending/processing
        if (in_array($order->status, ['pending', 'processing'])) {
            $order->status = 'shipped';
        }

        $order->save();

        return redirect()->back()->with('success', "Nomor resi {$order->tracking_number} berhasil diterbitkan Biteship dan kurir sudah diminta menjemput. Status diubah menjadi Dikirim.");
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
     * Memesan pengiriman ke Biteship: menerbitkan resi resmi sekaligus meminta
     * penjemputan kurir.
     *
     * Mengembalikan hasil yang MENYEBUTKAN ALASAN saat gagal, bukan sekadar
     * null. Sebelumnya kegagalan apa pun dijawab null, lalu pemanggilnya
     * menambal dengan nomor resi buatan sendiri dan tetap menandai pesanan
     * sebagai terkirim. Akibatnya pesanan terlihat berjalan padahal tidak ada
     * order di Biteship dan tidak ada kurir yang dipanggil — pembeli menunggu
     * paket yang tidak pernah dijemput, dan admin tidak diberi tahu apa pun.
     *
     * @return array{berhasil: bool, resi: ?string, alasan: ?string, soal_saldo: bool}
     */
    private function createBiteshipShipment(Order $order): array
    {
        $gagal = fn (string $alasan, bool $soalSaldo = false) => [
            'berhasil'   => false,
            'resi'       => null,
            'alasan'     => $alasan,
            'soal_saldo' => $soalSaldo,
        ];

        $apiKey = env('BITESHIP_API_KEY');

        if (! $apiKey) {
            return $gagal('Kunci API Biteship belum diisi di berkas .env.');
        }

        try {
            $address = $order->shipping_address;

            // Daftar kurir yang akan dicoba berurutan (jika satu gagal, lanjut berikutnya)
            // JNE paling stabil di sandbox BiteShip
            // Kurir diambil dari KODE resmi Biteship yang disimpan saat checkout (mis.
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

            // Jenis layanan diteruskan apa adanya ke Biteship.
            $jenisLayanan = $jenisLayanan ?: 'reg';

            // Kurir cadangan HANYA untuk pengiriman reguler.
            $instan = in_array($primaryCourier, ['gojek', 'grab', 'lalamove', 'borzo'], true);

            $courierFallbacks = $instan
                ? [$primaryCourier]
                : array_values(array_unique([$primaryCourier, 'jne', 'sicepat', 'anteraja']));

            // Berat dibaca dari satu tempat saja, supaya angka yang dilaporkan
            // ke Biteship selalu sama dengan yang tercetak di label.
            $beratSatuan = (int) config('pengiriman.berat_kirim_gram', 500);

            $items = $order->items->map(function ($item) use ($beratSatuan) {
                return [
                    'name'     => mb_substr($item->product_name ?? 'Produk', 0, 100),
                    'value'    => (int) $item->price,
                    'quantity' => (int) $item->quantity,
                    'weight'   => $beratSatuan,
                ];
            })->toArray();

            if (empty($items)) {
                $items = [['name' => 'Produk RECORD', 'value' => (int) $order->grand_total, 'quantity' => 1, 'weight' => $beratSatuan]];
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
                'origin_address'       => env('STORE_ADDRESS', 'Jln Kyai tambak deres 32, Kedungcowek, Bulak, Surabaya, Jawa Timur'),
                'origin_postal_code'   => (int) env('STORE_POSTAL_CODE', '60123'),

                'destination_contact_name'  => $address['recipient_name'] ?? ($order->user->name ?? 'Customer'),
                'destination_contact_phone' => $address['phone'] ?? ($order->user->phone ?? '08123456789'),
                'destination_name'          => $address['recipient_name'] ?? ($order->user->name ?? 'Customer'),
                'destination_phone'         => $address['phone'] ?? ($order->user->phone ?? '08123456789'),
                'destination_address'       => $destAddress ?: 'Indonesia',

                'courier_type'        => $jenisLayanan,
                'delivery_type'       => 'now',
                'items'               => $items,
            ];

            // Tambahkan titik koordinat penjemputan gudang agar akurat bagi kurir (khususnya instan & same-day)
            if (env('STORE_LATITUDE') && env('STORE_LONGITUDE')) {
                $basePayload['origin_latitude']  = (float) env('STORE_LATITUDE', -7.2275);
                $basePayload['origin_longitude'] = (float) env('STORE_LONGITUDE', 112.7865);
            }

            if ($postalCode) {
                $basePayload['destination_postal_code'] = (int) $postalCode;
            }

            /*
             * Asuransi pengiriman.
             *
             * Diaktifkan cukup dengan mengirim `courier_insurance` berisi nilai
             * barang — tidak ada tombol yang perlu dinyalakan di dasbor
             * Biteship. Langkah "centang opsi asuransi" di Pusat Bantuan mereka
             * berlaku untuk pesanan yang dibuat manual di dasbor, bukan lewat API.
             *
             * Preminya dipotong dari saldo bersamaan dengan ongkir, dan besarnya
             * dikembalikan Biteship di `courier.insurance.fee`.
             */
            $jasaAsuransi = app(\App\Services\AsuransiPengirimanService::class);
            $putusan = $jasaAsuransi->putuskan($order);

            if ($putusan['nilai'] > 0) {
                $basePayload['courier_insurance'] = $putusan['nilai'];
            }

            \Illuminate\Support\Facades\Log::info('Keputusan asuransi pengiriman', [
                'pesanan' => $order->order_number,
                'nilai'   => $putusan['nilai'],
                'alasan'  => $putusan['alasan'],
            ]);

            $saldo = app(\App\Services\SaldoBiteshipService::class);
            $galatTerakhir = 'Semua kurir yang dicoba menolak permintaan ini.';

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
                    $resi = $data['courier']['waybill_id'] ?? $data['id'] ?? null;

                    if (blank($resi)) {
                        return $gagal('Biteship menerima pesanan tetapi tidak mengembalikan nomor resi.');
                    }

                    // Simpan courier yang berhasil ke order
                    $order->courier = $courier;

                    /*
                     * Premi asuransi diambil dari jawaban Biteship, bukan
                     * dihitung sendiri dari persentase: tiap kurir punya tarif
                     * dan premi minimumnya masing-masing, jadi hanya angka dari
                     * merekalah yang benar.
                     */
                    $premi = (int) round((float) ($data['courier']['insurance']['fee'] ?? 0));
                    $nilaiDiasuransikan = (int) round((float) ($data['courier']['insurance']['amount'] ?? 0));

                    $order->shipping_insurance_fee   = $premi;
                    $order->shipping_insurance_value = $nilaiDiasuransikan;

                    /*
                     * Kode sortir dan id pesanan Biteship.
                     *
                     * Kode sortir dipakai gudang kurir untuk mengarahkan paket
                     * ke kota tujuan. Inilah yang membedakan label sah dari
                     * label karangan — tanpanya paket bisa salah sortir meski
                     * nomor resinya benar. Selama ini dikembalikan Biteship
                     * tetapi dibuang begitu saja.
                     */
                    $order->shipping_routing_code = $data['courier']['routing_code'] ?? null;
                    $order->biteship_order_id     = $data['id'] ?? null;

                    $jasaAsuransi->periksaPremi($order, $premi, $nilaiDiasuransikan);

                    // Berhasil berarti saldonya memang ada. Peringatan lama
                    // dicabut supaya tidak terus menakut-nakuti tanpa sebab.
                    $saldo->tandaiPulih();

                    return ['berhasil' => true, 'resi' => $resi, 'alasan' => null, 'soal_saldo' => false];
                }

                $errorBody = $response->json();
                $errorCode = $errorBody['code'] ?? 0;
                $errorPesan = $errorBody['error'] ?? $errorBody['message'] ?? $response->body();
                $galatTerakhir = (string) $errorPesan;

                // Saldo kurang tidak akan membaik dengan berganti kurir.
                if ($saldo->galatSoalSaldo($galatTerakhir, $errorCode)) {
                    $saldo->tandaiHabis($galatTerakhir);

                    \Illuminate\Support\Facades\Log::warning('Biteship menolak: saldo tidak cukup', [
                        'pesanan' => $order->order_number,
                    ]);

                    return $gagal('Saldo Biteship tidak cukup untuk membayar ongkir pengiriman ini.', true);
                }

                // Jika error bukan soal kurir (misal postal code salah), hentikan retry
                if (!in_array($errorCode, [40002031, 40002030, 40002032])) {
                    \Illuminate\Support\Facades\Log::warning('Biteship Fatal Error (no retry): ' . $response->body());
                    break;
                }

                \Illuminate\Support\Facades\Log::warning('Biteship Courier ' . $courier . ' not available, trying next...');
            }

            return $gagal($this->rapikanGalatBiteship($galatTerakhir));

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Biteship Exception: ' . $e->getMessage());

            return $gagal('Tidak bisa menghubungi Biteship: ' . $e->getMessage());
        }
    }

    /**
     * Menerjemahkan pesan galat Biteship menjadi kalimat yang bisa ditindak
     * admin. Pesan aslinya berbahasa Inggris dan berbentuk teknis; yang perlu
     * diketahui admin adalah apa yang harus dia perbaiki.
     */
    private function rapikanGalatBiteship(string $pesan): string
    {
        $p = strtolower($pesan);

        return match (true) {
            str_contains($p, 'postal')     => 'Kode pos alamat pembeli tidak dikenali Biteship. Perbaiki alamatnya lalu coba lagi.',
            str_contains($p, 'phone')      => 'Nomor telepon pengirim atau penerima tidak diterima Biteship.',
            str_contains($p, 'courier')    => 'Kurir yang dipilih tidak melayani rute ini. Ubah jasa kirimnya di detail pesanan.',
            str_contains($p, 'weight')     => 'Berat barang di luar batas yang dilayani kurir.',
            str_contains($p, 'address')    => 'Alamat pengiriman belum lengkap untuk diproses Biteship.',
            str_contains($p, 'unauthorized') || str_contains($p, 'api key')
                                           => 'Kunci API Biteship ditolak. Periksa BITESHIP_API_KEY.',
            default                        => 'Biteship menolak permintaan: ' . mb_substr($pesan, 0, 160),
        };
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

        $galat      = [];
        $soalSaldo  = false;

        foreach ($paidOrders as $order) {
            // Skip jika sudah shipped
            if ($order->status === 'shipped') {
                $skipped++;
                continue;
            }

            // Panggil API BiteShip untuk terbitkan Nomor Resi Resmi (AWB) & Request Pickup
            $hasil = $this->createBiteshipShipment($order);

            /*
             * Gagal berarti GAGAL.
             *
             * Dulu di sini diterbitkan nomor resi buatan sendiri dan pesanannya
             * tetap ditandai terkirim. Pesanan lalu terlihat berjalan di sistem
             * padahal tidak ada order apa pun di Biteship dan tidak ada kurir
             * yang dipanggil — pembeli menunggu paket yang tidak pernah
             * dijemput, dan admin tidak pernah tahu.
             *
             * Sekarang pesanannya dibiarkan apa adanya, siap dicoba lagi
             * setelah penyebabnya diperbaiki.
             */
            if (! $hasil['berhasil']) {
                $galat[$order->order_number] = $hasil['alasan'];
                $soalSaldo = $soalSaldo || $hasil['soal_saldo'];

                // Saldo kurang berlaku untuk semua pesanan; mencoba sisanya
                // hanya membuang waktu dan menumpuk pesan yang sama.
                if ($hasil['soal_saldo']) {
                    break;
                }

                continue;
            }

            $order->tracking_number = $hasil['resi'];
            $order->status = 'shipped';
            $order->save();
            $count++;
        }

        $pesan = [];

        if ($count > 0) {
            $pesan[] = "{$count} pesanan berhasil diproses. Nomor resi terbit dan kurir sudah diminta menjemput.";
        }

        if ($skipped > 0) {
            $pesan[] = "{$skipped} pesanan dilewati karena sudah berstatus dikirim.";
        }

        if ($unpaidOrders->isNotEmpty()) {
            $pesan[] = $unpaidOrders->count() . ' pesanan dilewati karena belum lunas: '
                . $unpaidOrders->pluck('order_number')->join(', ') . '.';
        }

        if ($galat !== []) {
            $rincian = collect($galat)
                ->map(fn ($alasan, $nomor) => $nomor . ' — ' . $alasan)
                ->join(' | ');

            $awalan = $soalSaldo
                ? 'Pengiriman dihentikan karena saldo Biteship tidak cukup. Isi saldo lalu ulangi. '
                : count($galat) . ' pesanan GAGAL dikirim dan statusnya tidak diubah. ';

            return redirect()->route('admin.orders')
                ->with('error', $awalan . $rincian)
                ->with('info', $pesan === [] ? null : implode(' ', $pesan));
        }

        return redirect()->route('admin.orders')->with('success', implode(' ', $pesan));
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
