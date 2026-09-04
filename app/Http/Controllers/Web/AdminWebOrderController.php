<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderExport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminWebOrderController extends Controller
{
    /**
     * Tampilkan daftar semua pesanan masuk dengan filter status, tanggal, tipe kurir & pencarian komprehensif ala Shopee.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product', 'returns']);

        // 1. Filter Tab Status Utama
        $currentTab = $request->query('tab', 'all');
        $this->applyTabScope($query, $currentTab);

        // 2. Filter Sub-Status (Perlu diproses vs Telah diproses)
        if ($request->filled('sub_status') && $request->sub_status !== 'all') {
            if ($request->sub_status === 'unprocessed') {
                $query->where(function ($q) {
                    $q->whereNull('tracking_number')
                      ->orWhere('tracking_number', '')
                      ->orWhere('tracking_number', 'like', 'REC-%');
                });
            } elseif ($request->sub_status === 'processed') {
                $query->whereNotNull('tracking_number')
                      ->where('tracking_number', '!=', '')
                      ->where('tracking_number', 'not like', 'REC-%');
            }
        }

        // 3. Filter Tipe Pesanan / Pengiriman (Reguler, Instant, Kilat/Kargo)
        if ($request->filled('shipping_type') && $request->shipping_type !== 'all') {
            switch ($request->shipping_type) {
                case 'instant':
                    $this->scopeInstant($query);
                    break;
                case 'cargo':
                    $this->scopeCargo($query);
                    break;
                case 'reguler':
                    $this->scopeReguler($query);
                    break;
            }
        }

        // 4. Filter Jasa Kirim (Ekspedisi Kurir)
        if ($request->filled('courier') && $request->courier !== 'all') {
            $courier = strtolower(trim($request->courier));
            $query->where(function ($q) use ($courier) {
                $q->where('courier', 'like', "%{$courier}%")
                  ->orWhere('courier_code', 'like', "%{$courier}%");
            });
        }

        // 5. Filter Berdasarkan Hari / Tanggal
        if ($request->filled('date_filter') && $request->date_filter !== 'all') {
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', Carbon::yesterday());
                    break;
                case 'last_7_days':
                    $query->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay());
                    break;
                case 'last_30_days':
                    $query->where('created_at', '>=', Carbon::now()->subDays(30)->startOfDay());
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', Carbon::now()->month)
                          ->whereYear('created_at', Carbon::now()->year);
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
                    }
                    if ($request->filled('end_date')) {
                        $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
                    }
                    break;
            }
        }

        // 6. Pencarian Komprehensif Berdasarkan Tipe
        if ($request->filled('search')) {
            $search = trim($request->search);
            $searchType = $request->query('search_type', 'all');

            $query->where(function ($q) use ($search, $searchType) {
                if ($searchType === 'order_number') {
                    $q->where('order_number', 'like', "%{$search}%");
                } elseif ($searchType === 'tracking_number') {
                    $q->where('tracking_number', 'like', "%{$search}%");
                } elseif ($searchType === 'buyer') {
                    $q->whereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                    })->orWhere('shipping_address', 'like', "%{$search}%");
                } elseif ($searchType === 'product') {
                    $q->whereHas('items', function ($i) use ($search) {
                        $i->where('product_name', 'like', "%{$search}%")
                          ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
                    });
                } elseif ($searchType === 'sku') {
                    $q->whereHas('items', function ($i) use ($search) {
                        $i->whereHas('product.variants', fn ($v) => $v->where('sku', 'like', "%{$search}%"));
                    });
                } else {
                    // All / Default
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('tracking_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($u) use ($search) {
                          $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      })
                      ->orWhereHas('items', function ($i) use ($search) {
                          $i->where('product_name', 'like', "%{$search}%");
                      });
                }
            });
        }

        // 7. Pengurutan Data (Sorting)
        $sort = $request->query('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'amount_high':
                $query->orderByDesc('grand_total');
                break;
            case 'amount_low':
                $query->orderBy('grand_total', 'asc');
                break;
            case 'latest':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        // Auto-sync status pembayaran pesanan unpaid dengan Midtrans API
        $this->syncMidtransPaymentStatus(Order::whereIn('payment_status', ['unpaid', 'pending_verification'])->where('payment_method', '!=', 'COD')->limit(10)->get());

        $orders = $query->paginate(15)->withQueryString();

        // Riwayat unduhan untuk modal Riwayat Download
        $riwayatEkspor = OrderExport::with('user')
            ->latest()
            ->take(OrderExport::BATAS_RIWAYAT)
            ->get();

        // Hitungan Tab Utama
        $counts = [
            'all'       => Order::count(),
            'ready'     => Order::where('payment_status', 'paid')->whereIn('status', ['pending', 'processing'])->count(),
            'unpaid'    => Order::whereIn('payment_status', ['unpaid', 'pending_verification'])->count(),
            'shipped'   => Order::where('status', 'shipped')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where(function ($q) {
                $q->where('status', 'cancelled')->orWhereHas('returns');
            })->count(),
        ];

        // Base query untuk hitungan sub-filter pada tab yang aktif saat ini
        $currentTabBaseQuery = Order::query();
        $this->applyTabScope($currentTabBaseQuery, $currentTab);

        // Hitungan Sub-Filter (Disesuaikan secara presisi dengan tab aktif saat ini)
        $subCounts = [
            'ready_unprocessed' => (clone $currentTabBaseQuery)
                ->where(function ($q) {
                    $q->whereNull('tracking_number')
                      ->orWhere('tracking_number', '')
                      ->orWhere('tracking_number', 'like', 'REC-%');
                })->count(),
            'ready_processed' => (clone $currentTabBaseQuery)
                ->whereNotNull('tracking_number')
                ->where('tracking_number', '!=', '')
                ->where('tracking_number', 'not like', 'REC-%')
                ->count(),
            'reguler'        => $this->scopeReguler(clone $currentTabBaseQuery)->count(),
            'instant'        => $this->scopeInstant(clone $currentTabBaseQuery)->count(),
            'cargo'          => $this->scopeCargo(clone $currentTabBaseQuery)->count(),
            'global_instant' => $this->scopeInstant(Order::query())->count(),
        ];

        return view('admin.orders', [
            'orders'        => $orders,
            'counts'        => $counts,
            'subCounts'     => $subCounts,
            'currentTab'    => $currentTab,
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
        $order = Order::with(['user', 'items.product', 'items.productVariant'])
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id)->orWhere('order_number', $id);
                } else {
                    $q->where('order_number', $id);
                }
            })
            ->firstOrFail();

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
        // Hanya proses jika pesanan sudah lunas
        if ($order->payment_status !== 'paid' || in_array($order->status, ['cancelled', 'completed'])) {
            return redirect()->back()->with('error', 'Pesanan ini belum lunas atau telah dibatalkan. Hanya pesanan yang sudah lunas yang dapat diproses pengirimannya.');
        }

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

        $apiKey = config('biteship.api_key', env('BITESHIP_API_KEY'));

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

            // Normalisasi alias nama kurir ke format resmi Biteship
            if (in_array(strtolower((string)$primaryCourier), ['gosend', 'go-send'])) {
                $primaryCourier = 'gojek';
            } elseif (in_array(strtolower((string)$primaryCourier), ['grabexpress', 'grab-express'])) {
                $primaryCourier = 'grab';
            }

            $primaryCourier = $primaryCourier ?: 'jne';

            $instan = in_array($primaryCourier, ['gojek', 'grab', 'lalamove', 'borzo'], true);

            // Jenis layanan default: untuk instan gunakan 'instant', untuk reguler gunakan 'reg'
            $jenisLayanan = $jenisLayanan ?: ($instan ? 'instant' : 'reg');

            // Kurir cadangan: tetap dalam kategori yang sama (instan hanya sesama instan: Gojek/Grab)
            $courierFallbacks = $instan
                ? array_values(array_unique([$primaryCourier, 'gojek', 'grab']))
                : array_values(array_unique([$primaryCourier, 'anteraja', 'jne', 'sicepat']));

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

            $destLat = $address['latitude'] ?? null;
            $destLng = $address['longitude'] ?? null;

            // Jika koordinat belum tersimpan di snapshot alamat pesanan, ambil dari alamat tersimpan customer
            if ((!$destLat || !$destLng) && !empty($order->user_id)) {
                $userAddr = \App\Models\Address::where('user_id', $order->user_id)->orderByDesc('is_default')->first();
                if ($userAddr && $userAddr->latitude && $userAddr->longitude) {
                    $destLat = $userAddr->latitude;
                    $destLng = $userAddr->longitude;
                }
            }

            // Koordinat gudang asal toko (Surabaya)
            $originLat = (float) env('STORE_LATITUDE', -7.2275);
            $originLng = (float) env('STORE_LONGITUDE', 112.7865);

            $basePayload = [
                'shipper_name'          => env('STORE_LABEL', 'RECORD Official Store'),
                'shipper_contact_name'  => env('STORE_LABEL', 'RECORD Official Store'),
                'shipper_phone'         => env('STORE_PHONE', '081323065554'),
                'origin_contact_name'   => env('STORE_LABEL', 'RECORD Official Store'),
                'origin_contact_phone'  => env('STORE_PHONE', '081323065554'),
                'origin_address'        => env('STORE_ADDRESS', 'Jln Kyai tambak deres 32, Kedungcowek, Bulak, Surabaya, Jawa Timur'),
                'origin_postal_code'    => (int) env('STORE_POSTAL_CODE', '60123'),
                'origin_coordinate'     => [
                    'latitude'  => $originLat,
                    'longitude' => $originLng,
                ],

                'destination_contact_name'  => $address['recipient_name'] ?? ($order->user->name ?? 'Customer'),
                'destination_contact_phone' => $address['phone'] ?? ($order->user->phone ?? '08123456789'),
                'destination_name'          => $address['recipient_name'] ?? ($order->user->name ?? 'Customer'),
                'destination_phone'         => $address['phone'] ?? ($order->user->phone ?? '08123456789'),
                'destination_address'       => $destAddress ?: 'Indonesia',

                'delivery_type'       => 'now',
                'items'               => $items,
            ];

            if ($postalCode) {
                $basePayload['destination_postal_code'] = (int) $postalCode;
            }

            // Sertakan koordinat tujuan (wajib untuk instan Gojek / Grab)
            if ($destLat && $destLng) {
                $basePayload['destination_coordinate'] = [
                    'latitude'  => (float) $destLat,
                    'longitude' => (float) $destLng,
                ];
            } elseif ($instan) {
                // Fallback koordinat tujuan terdekat di Surabaya jika customer tidak mengizinkan akses GPS
                $basePayload['destination_coordinate'] = [
                    'latitude'  => $originLat - 0.014,
                    'longitude' => $originLng - 0.021,
                ];
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
                $curType = in_array($courier, ['gojek', 'grab', 'lalamove', 'borzo']) ? 'instant' : 'reg';
                $payload = array_merge($basePayload, [
                    'courier_company' => $courier,
                    'courier_type'    => $curType,
                ]);

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

                    // Simpan nama kurir dan layanan yang serasi dengan sistem Biteship
                    $comp = strtolower($data['courier']['company'] ?? $courier);
                    $type = strtolower($data['courier']['type'] ?? $curType);

                    if ($comp === 'gojek') {
                        $order->courier = 'Gojek Instant';
                    } elseif ($comp === 'grab') {
                        $order->courier = 'GrabExpress Instant';
                    } elseif ($comp === 'anteraja') {
                        $order->courier = 'AnterAja ' . ($type === 'instant' ? 'Instant' : 'Reguler');
                    } elseif ($comp === 'jne') {
                        $order->courier = 'JNE ' . ($type === 'instant' ? 'Instant' : 'Reguler');
                    } elseif ($comp === 'sicepat') {
                        $order->courier = 'SiCepat ' . ($type === 'instant' ? 'Instant' : 'Reguler');
                    } elseif ($comp === 'jnt' || $comp === 'j&t') {
                        $order->courier = 'J&T ' . ($type === 'instant' ? 'Instant' : 'Reguler');
                    } else {
                        $order->courier = strtoupper($comp) . ($type ? ' ' . ucfirst($type) : '');
                    }

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

                // Jika error fatal seperti unauthorized (kunci API salah), baru hentikan retry
                if (in_array($errorCode, [40100001, 40100002, 40300001]) || str_contains(strtolower($galatTerakhir), 'unauthorized')) {
                    \Illuminate\Support\Facades\Log::warning('Biteship Fatal Auth Error (no retry): ' . $response->body());
                    break;
                }

                \Illuminate\Support\Facades\Log::warning('Biteship Courier ' . $courier . ' (' . $curType . ') failed: ' . $galatTerakhir . ', trying next fallback...');
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

        // Hanya cetak pesanan yang sudah lunas
        $orders = Order::with(['user', 'items.product', 'items.productVariant'])
            ->whereIn('id', $request->order_ids)
            ->where('payment_status', 'paid')
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada pesanan lunas yang dipilih untuk dicetak resinya.');
        }

        return view('admin.partials.order-bulk-print', compact('orders'));
    }

    /**
     * Hapus satu pesanan (Khusus Super Admin).
     */
    public function destroy(int $id)
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin() && !$user->hasRole('super_admin') && !$user->hasRole('Super Admin') && !$user->hasRole('admin'))) {
            return redirect()->back()->with('error', 'Akses ditolak: Hanya Super Admin yang berhak menghapus pesanan.');
        }

        $order = Order::findOrFail($id);
        $orderNumber = $order->order_number;

        // Hapus relasi items dan returns terlebih dahulu
        $order->items()->delete();
        $order->returns()->delete();
        $order->delete();

        return redirect()->route('admin.orders')->with('success', "Pesanan #{$orderNumber} berhasil dihapus permanen oleh Super Admin.");
    }

    /**
     * Hapus banyak pesanan sekaligus (Bulk Delete - Khusus Super Admin).
     */
    public function bulkDelete(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin() && !$user->hasRole('super_admin') && !$user->hasRole('Super Admin') && !$user->hasRole('admin'))) {
            return redirect()->back()->with('error', 'Akses ditolak: Hanya Super Admin yang berhak menghapus pesanan.');
        }

        $request->validate([
            'order_ids'   => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
        ]);

        $orders = Order::whereIn('id', $request->order_ids)->get();
        $count = $orders->count();

        foreach ($orders as $order) {
            $order->items()->delete();
            $order->returns()->delete();
            $order->delete();
        }

        return redirect()->route('admin.orders')->with('success', "{$count} pesanan berhasil dihapus permanen oleh Super Admin.");
    }

    /**
     * Unduh berkas invoice resmi berformat PDF untuk admin.
     */
    public function downloadInvoicePdf($id)
    {
        $order = \App\Models\Order::with(['items.product', 'items.productVariant', 'user'])
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id)->orWhere('order_number', $id);
                } else {
                    $q->where('order_number', $id);
                }
            })
            ->firstOrFail();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.pdf.invoice', ['order' => $order])
            ->setPaper('a4', 'portrait');

        $filename = 'Invoice-' . ($order->invoice_number ?: $order->order_number) . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Terapkan scope filter tab status utama.
     */
    private function applyTabScope($query, string $tab)
    {
        switch ($tab) {
            case 'ready':
                // Perlu Dikirim: Lunas & status pending/processing
                $query->where('payment_status', 'paid')->whereIn('status', ['pending', 'processing']);
                break;
            case 'unpaid':
                // Belum bayar
                $query->whereIn('payment_status', ['unpaid', 'pending_verification']);
                break;
            case 'shipped':
                // Sedang dikirim
                $query->where('status', 'shipped');
                break;
            case 'completed':
                // Selesai
                $query->where('status', 'completed');
                break;
            case 'cancelled':
                // Pengembalian / Pembatalan
                $query->where(function ($q) {
                    $q->where('status', 'cancelled')
                      ->orWhereHas('returns');
                });
                break;
            case 'all':
            default:
                break;
        }
    }

    /**
     * Scope query untuk mendeteksi pesanan instant / sameday.
     */
    private function scopeInstant($query)
    {
        return $query->where(function ($q) {
            $q->where('courier', 'like', '%instant%')
              ->orWhere('courier', 'like', '%sameday%')
              ->orWhere('courier', 'like', '%same day%')
              ->orWhere('courier', 'like', '%same_day%')
              ->orWhere('courier', 'like', '%gojek%')
              ->orWhere('courier', 'like', '%grab%')
              ->orWhere('courier', 'like', '%gosend%')
              ->orWhere('courier', 'like', '%lalamove%')
              ->orWhere('courier', 'like', '%borzo%')
              ->orWhere('courier_code', 'like', '%instant%')
              ->orWhere('courier_code', 'like', '%sameday%')
              ->orWhere('courier_code', 'like', '%same_day%')
              ->orWhere('courier_code', 'like', '%gojek%')
              ->orWhere('courier_code', 'like', '%grab%')
              ->orWhere('courier_code', 'like', '%gosend%')
              ->orWhere('courier_code', 'like', '%lalamove%')
              ->orWhere('courier_code', 'like', '%borzo%');
        });
    }

    /**
     * Scope query untuk mendeteksi pesanan cargo / kargo.
     */
    private function scopeCargo($query)
    {
        return $query->where(function ($q) {
            $q->where('courier', 'like', '%cargo%')
              ->orWhere('courier', 'like', '%kargo%')
              ->orWhere('courier_code', 'like', '%cargo%')
              ->orWhere('courier_code', 'like', '%kargo%');
        });
    }

    /**
     * Scope query untuk pesanan reguler (bukan instant dan bukan cargo).
     */
    private function scopeReguler($query)
    {
        return $query->where(function ($q) {
            $q->whereNot(function ($sub) {
                $this->scopeInstant($sub);
            })->whereNot(function ($sub) {
                $this->scopeCargo($sub);
            });
        });
    }
}
