@extends('layouts.app')

@section('title', 'Detail Pesanan #' . $order->order_number)
@section('page_title', 'Detail Pesanan #' . $order->order_number)

@section('content')

<div class="space-y-6">

    {{-- Back + Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.orders') }}" class="text-xs font-bold text-gray-500 hover:text-orange-600 transition inline-flex items-center gap-1.5 mb-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Pesanan
            </a>
            <h1 class="text-xl font-black text-slate-900 tracking-wide">
                Pesanan <span class="font-mono text-orange-600">#{{ $order->order_number }}</span>
            </h1>
            <p class="text-xs text-gray-400 mt-0.5">Dibuat pada {{ $order->created_at->format('d M Y, H:i:s') }} WIB</p>
        </div>

        <div class="flex items-center gap-2">
            {{-- Print Resi Button --}}
            <a href="{{ route('admin.orders.show', $order->id) }}?print=1" target="_blank"
               class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold text-xs px-4 py-2 rounded-xl transition border border-gray-200">
                <i class="fa-solid fa-print"></i> Cetak Resi
            </a>
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider
                @if($order->status === 'pending') bg-amber-100 text-amber-800 border border-amber-200
                @elseif($order->status === 'processing') bg-blue-100 text-blue-800 border border-blue-200
                @elseif($order->status === 'shipped') bg-indigo-100 text-indigo-800 border border-indigo-200
                @elseif($order->status === 'completed') bg-emerald-100 text-emerald-800 border border-emerald-200
                @else bg-rose-100 text-rose-800 border border-rose-200
                @endif">
                Status: {{ strtoupper($order->status) }}
            </span>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flash-alert p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-emerald-500 hover:text-emerald-700 transition p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    {{-- Alert Payment Pending Verification --}}
    @if($order->payment_status === 'pending_verification')
        <div class="p-4 rounded-xl bg-amber-50 border-2 border-amber-300 text-amber-900 text-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-clock text-amber-500 text-lg"></i>
                <div>
                    <strong>Pembayaran Menunggu Konfirmasi!</strong><br>
                    <span class="text-xs">Customer sudah klik "Konfirmasi Saya Sudah Bayar". Cek rekening/mutasi Anda, lalu klik Konfirmasi Lunas.</span>
                </div>
            </div>
            <form action="{{ route('admin.orders.confirm-payment', $order->id) }}" method="POST" class="shrink-0">
                @csrf
                @method('PATCH')
                <button type="submit" onclick="return confirm('Konfirmasi pembayaran pesanan ini sudah masuk dan LUNAS?')"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition shadow-sm uppercase tracking-wider">
                    <i class="fa-solid fa-check mr-1"></i> Konfirmasi Lunas Sekarang
                </button>
            </form>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        {{-- Left: Items + Address --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- ══════════ Ringkasan Pesanan ══════════
                 Diletakkan paling atas: tiga hal yang paling sering dicari
                 admin saat membuka satu pesanan — nomornya, ke mana dikirim,
                 dan paketnya sedang di mana. Rincian uangnya menyusul di
                 bawah.

                 Berbeda dari acuan Shopee, nama dan nomor telepon di sini
                 TIDAK disamarkan. Shopee menyamarkannya karena penjual di
                 sana orang lain bagi pembeli; di panel ini adminnya adalah
                 toko itu sendiri, dan data lengkap justru yang dibutuhkan
                 untuk menghubungi pembeli saat paket bermasalah. --}}
            @php
                $alamatKirim = (array) $order->shipping_address;

                // Koordinat tujuan: dari salinan di pesanan lebih dulu,
                // baru dari alamat pembeli sebagai cadangan untuk pesanan lama.
                $lintangTujuan = $alamatKirim['latitude']  ?? null;
                $bujurTujuan   = $alamatKirim['longitude'] ?? null;

                if (blank($lintangTujuan) && $order->user) {
                    $cadanganAlamat = $order->user->addresses()
                        ->whereNotNull('latitude')->orderByDesc('is_default')->first();

                    $lintangTujuan = $cadanganAlamat->latitude  ?? null;
                    $bujurTujuan   = $cadanganAlamat->longitude ?? null;
                }

                $tujuanTeks = trim(collect([
                    $alamatKirim['address_line'] ?? null,
                    $alamatKirim['city'] ?? null,
                    $alamatKirim['province'] ?? null,
                    $alamatKirim['postal_code'] ?? null,
                ])->filter()->implode(', '));

                // Jenis layanan dibaca dari kode kurir yang disimpan saat
                // checkout, bukan ditebak dari namanya.
                [$kodeKurirPesanan, $jenisKirim] = array_pad(
                    explode(':', (string) $order->courier_code, 2), 2, null
                );

                $adalahInstan = in_array($kodeKurirPesanan, ['gojek', 'grab', 'lalamove', 'borzo'], true)
                    || str_contains(strtolower((string) $jenisKirim), 'instant');

                $jumlahBarang = $order->items->sum('quantity');
            @endphp

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">

                {{-- No. Pesanan --}}
                <div class="flex items-start gap-3">
                    <span class="w-7 h-7 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-hashtag text-[11px]"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-black text-slate-900">No. Pesanan</p>
                        <p class="font-mono text-sm text-gray-600 mt-0.5 select-all">{{ $order->order_number }}</p>
                        @if($order->invoice_number)
                            <p class="text-[10px] text-gray-400 mt-0.5">Invoice {{ $order->invoice_number }}</p>
                        @endif
                    </div>
                </div>

                {{-- Alamat Pengiriman --}}
                <div class="flex items-start gap-3 border-t border-gray-50 pt-4">
                    <span class="w-7 h-7 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-location-dot text-[11px]"></i>
                    </span>
                    <div class="min-w-0 text-xs">
                        <p class="font-black text-slate-900">Alamat Pengiriman</p>
                        <p class="text-gray-700 mt-1">
                            <span class="font-bold">{{ $alamatKirim['recipient_name'] ?? $order->user->name ?? '—' }}</span>
                            <span class="text-gray-500">
                                ({{ $alamatKirim['phone'] ?? $order->user->phone ?? 'tanpa nomor' }})
                            </span>
                        </p>
                        <p class="text-gray-600 leading-relaxed mt-0.5">{{ $tujuanTeks ?: '—' }}</p>
                    </div>
                </div>

                {{-- Informasi Jasa Kirim --}}
                <div class="flex items-start gap-3 border-t border-gray-50 pt-4">
                    <span class="w-7 h-7 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-truck text-[11px]"></i>
                    </span>
                    <div class="min-w-0 flex-1 text-xs space-y-3">
                        <p class="font-black text-slate-900">Informasi Jasa Kirim</p>

                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-gray-700">Paket 1:</span>

                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                {{ $adalahInstan
                                    ? 'bg-rose-50 text-rose-700 border border-rose-200'
                                    : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                {{ $adalahInstan ? 'Instant' : 'Reguler' }}
                            </span>

                            <span class="text-gray-600">{{ $order->courier ?: '—' }}</span>

                            @if($order->tracking_number)
                                <span class="font-mono text-gray-500 select-all">({{ $order->tracking_number }})</span>
                            @else
                                <span class="text-gray-400 italic">belum ada resi</span>
                            @endif
                        </div>

                        {{-- Penerima + jumlah barang --}}
                        <div class="flex items-center gap-3 flex-wrap">
                            @php $barangPertama = $order->items->first(); @endphp
                            @if($barangPertama)
                                <img src="{{ $barangPertama->product?->image_url ?? 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=120&q=80' }}"
                                     alt="{{ $barangPertama->product_name }}"
                                     class="w-10 h-10 rounded-lg object-cover bg-gray-100 border border-gray-200 shrink-0"
                                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=120&q=80';">
                            @endif
                            <span class="text-gray-600">
                                Total {{ $jumlahBarang }} produk
                                @if($order->items->count() > 1)
                                    <span class="text-gray-400">({{ $order->items->count() }} jenis)</span>
                                @endif
                            </span>
                        </div>

                        {{-- Posisi paket. Komponennya dipindah ke sini supaya
                             seluruh keterangan pengiriman berada dalam satu
                             blok, sebagaimana acuan yang kamu berikan. --}}
                        <x-lacak-paket
                            :alamat="route('admin.pelacakan.pesanan', $order->id)"
                            :resi="$order->tracking_number"
                            :kurir="$order->courier"
                            :lintang="$lintangTujuan"
                            :bujur="$bujurTujuan"
                            :tujuan="$tujuanTeks"
                            judul="Posisi Paket ke Pembeli"
                            nada="slate" />
                    </div>
                </div>
            </div>


            {{-- ══════════ Informasi Pembayaran ══════════
                 Disusun seperti panel penghasilan di Seller Center: daftar
                 barang dulu, lalu rincian potongan berurut sampai angka yang
                 benar-benar diterima toko.

                 Bedanya dengan Shopee, potongannya bukan biaya platform —
                 melainkan yang memang berlaku di sistem ini: biaya Midtrans,
                 ongkir yang dibayarkan ke kurir lewat Biteship, dan komisi
                 referal. --}}
            @php
                $lunas      = $order->payment_status === 'paid';
                $jasaBiaya  = app(\App\Services\TransactionFeeService::class);

                $barang     = (int) round((float) $order->total_price);
                $ongkirBeli = (int) round((float) $order->shipping_cost);
                $diskon     = (int) round((float) ($order->referral_discount ?? 0));
                $dibayar    = (int) round((float) $order->grand_total);
                $komisi     = (int) round((float) ($order->referral_commission ?? 0));

                /*
                 * Angka dianggap TERCATAT hanya bila biayanya benar-benar
                 * pernah dihitung. Pesanan lunas dari sebelum pencatatan biaya
                 * dipasang punya midtrans_fee 0 dan ongkir asli 0 — kalau itu
                 * dipakai apa adanya, potongannya tampil "-Rp 0" dan hasil
                 * bersihnya sama dengan seluruh uang pembeli. Angka yang
                 * terlalu besar seperti itu lebih berbahaya daripada angka
                 * yang jujur disebut perkiraan.
                 */
                $tercatat = $lunas
                    && $order->net_revenue !== null
                    && (float) $order->midtrans_fee > 0;

                if ($tercatat) {
                    $biayaBayar = (int) round((float) $order->midtrans_fee);
                    $ongkirAsli = (int) round((float) ($order->shipping_actual_cost ?: 0));
                    $bersih     = (int) round((float) $order->net_revenue);
                } else {
                    /*
                     * Pesanan yang belum lunas belum punya angka tercatat, jadi
                     * dihitung sebagai perkiraan. Ditandai jelas supaya tidak
                     * disangka angka final — kanal pembayaran bisa berubah dan
                     * biayanya ikut berubah.
                     */
                    $biayaBayar = $jasaBiaya->hitungBiayaMidtrans((string) $order->payment_method, $dibayar);
                    /*
                     * Dibandingkan sebagai ANGKA, bukan lewat "?:".
                     *
                     * Kolom desimal dikembalikan sebagai teks "0.00", dan teks
                     * itu dianggap BENAR oleh PHP — hanya "0" dan "" yang
                     * dianggap salah. Akibatnya cadangannya tidak pernah
                     * terpakai dan ongkir ke kurir selalu tampil Rp 0.
                     */
                    $ongkirTercatat = (float) $order->shipping_actual_cost;
                    $ongkirAsli = (int) round($ongkirTercatat > 0
                        ? $ongkirTercatat
                        : (float) $order->shipping_cost);
                    $bersih     = $dibayar - $biayaBayar - $ongkirAsli - $komisi;
                }

                $markup = max(0, $ongkirBeli - $ongkirAsli);
            @endphp

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-orange-500"></i>
                        Informasi Pembayaran
                    </h3>
                    @if(! $tercatat)
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider">
                            {{ $lunas ? 'Perkiraan — biaya belum tercatat' : 'Perkiraan — belum lunas' }}
                        </span>
                    @endif
                </div>

                {{-- Daftar barang --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50/70 text-[10px] uppercase tracking-wider text-gray-400">
                            <tr>
                                <th class="px-4 py-2.5 w-10">No.</th>
                                <th class="px-4 py-2.5">Produk</th>
                                <th class="px-4 py-2.5 text-right whitespace-nowrap">Harga Satuan</th>
                                <th class="px-4 py-2.5 text-center">Jumlah</th>
                                <th class="px-4 py-2.5 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($order->items as $i => $item)
                                <tr>
                                    <td class="px-4 py-3 text-gray-400 align-top">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-start gap-3">
                                            <img src="{{ $item->product?->image_url ?? 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=120&q=80' }}"
                                                 alt="{{ $item->product_name }}"
                                                 class="w-11 h-11 rounded-lg object-cover bg-gray-100 border border-gray-200 shrink-0"
                                                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=120&q=80';">
                                            <div class="min-w-0">
                                                <p class="text-gray-800 leading-snug line-clamp-2">{{ $item->product_name }}</p>
                                                @if($item->variant_info)
                                                    <p class="text-[10px] text-gray-400 mt-0.5">Variasi: {{ $item->variant_info }}</p>
                                                @endif
                                                @php $sku = $item->productVariant->sku ?? null; @endphp
                                                @if($sku)
                                                    <p class="text-[10px] text-gray-400 font-mono">Kode Variasi: {{ $sku }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600 whitespace-nowrap align-top">
                                        {{ number_format($item->price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-600 align-top">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-800 whitespace-nowrap align-top">
                                        {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-400 italic">
                                        Tidak ada rincian barang pada pesanan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Rincian sampai penghasilan bersih --}}
                <div class="px-6 py-5 border-t border-gray-100">
                    <div class="ml-auto w-full sm:max-w-md space-y-2 text-xs">

                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal Pesanan</span>
                            <span>Rp {{ number_format($barang, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between text-gray-600">
                            <span>
                                Ongkos Kirim Ditagih
                                @if($markup > 0)
                                    <span class="block text-[10px] text-gray-400">
                                        termasuk markup Rp {{ number_format($markup, 0, ',', '.') }}
                                    </span>
                                @endif
                            </span>
                            <span>Rp {{ number_format($ongkirBeli, 0, ',', '.') }}</span>
                        </div>

                        @if($diskon > 0)
                            <div class="flex justify-between text-gray-600">
                                <span>
                                    Diskon Referal
                                    @if($order->referral_code_used)
                                        <span class="block text-[10px] text-gray-400 font-mono">{{ $order->referral_code_used }}</span>
                                    @endif
                                </span>
                                <span class="text-rose-600">−Rp {{ number_format($diskon, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between font-black text-sm text-slate-900 border-t border-gray-200 pt-2">
                            <span>Dibayar Pembeli</span>
                            <span class="text-orange-600">Rp {{ number_format($dibayar, 0, ',', '.') }}</span>
                        </div>

                        {{-- Potongan: uang yang masuk rekening tapi harus
                             diteruskan ke pihak lain. --}}
                        <div class="pt-2 space-y-2">
                            <div class="flex justify-between text-rose-600">
                                <span>
                                    Biaya Midtrans
                                    <span class="block text-[10px] text-gray-400 font-normal">
                                        {{ $jasaBiaya->labelBiaya((string) $order->payment_method) }}
                                    </span>
                                </span>
                                <span class="shrink-0">−Rp {{ number_format($biayaBayar, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between text-rose-600">
                                <span>
                                    Ongkir ke Kurir
                                    <span class="block text-[10px] text-gray-400 font-normal">
                                        {{ $order->courier ?: 'kurir' }} lewat Biteship
                                    </span>
                                </span>
                                <span class="shrink-0">−Rp {{ number_format($ongkirAsli, 0, ',', '.') }}</span>
                            </div>

                            @if($komisi > 0)
                                <div class="flex justify-between text-rose-600">
                                    <span>Komisi Referal</span>
                                    <span>−Rp {{ number_format($komisi, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-between items-center border-t-2 border-gray-200 pt-3 mt-1">
                            <span class="font-black text-slate-900 text-sm">
                                {{ $tercatat ? 'Penghasilan Bersih' : 'Estimasi Penghasilan Bersih' }}
                            </span>
                            <span class="font-black text-lg {{ $tercatat ? 'text-emerald-600' : 'text-amber-600' }}">
                                Rp {{ number_format($bersih, 0, ',', '.') }}
                            </span>
                        </div>

                        {{-- Perlu disebut terang-terangan: modal barang tidak
                             ikut dipotong, sebab sistem ini tidak menyimpan
                             harga pokok per produk. Tanpa keterangan ini,
                             angka di atas mudah disangka laba bersih. --}}
                        <p class="text-[10px] text-gray-400 leading-relaxed pt-1">
                            Belum dikurangi modal barang — sistem belum menyimpan harga pokok per produk.
                            Angka ini uang yang tinggal di rekening toko setelah Midtrans dan kurir dibayar.
                        </p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right: Actions --}}
        <div class="space-y-6">

            {{-- Alasan pembatalan dari pembeli --}}
            @if($order->status === 'cancelled' && $order->cancellation_reason)
                <div class="bg-rose-50 rounded-2xl border-2 border-rose-200 shadow-sm p-6 space-y-3">
                    <h3 class="text-xs font-black text-rose-800 uppercase tracking-wider border-b border-rose-200 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-circle-xmark text-rose-500"></i>
                        Alasan Pembatalan
                    </h3>

                    <p class="text-sm font-bold text-rose-900">{{ $order->cancellation_reason }}</p>

                    @if($order->cancellation_note)
                        <div class="bg-white/70 border border-rose-200 rounded-xl p-3">
                            <p class="text-[10px] font-black uppercase tracking-wider text-rose-500 mb-1">Catatan Pembeli</p>
                            <p class="text-xs text-rose-800 leading-relaxed">"{{ $order->cancellation_note }}"</p>
                        </div>
                    @endif

                    @if($order->cancelled_at)
                        <p class="text-[11px] text-rose-500 font-semibold">
                            <i class="fa-solid fa-clock mr-1"></i>
                            Dibatalkan {{ $order->cancelled_at->translatedFormat('d F Y, H:i') }} WIB
                            ({{ $order->cancelled_at->diffForHumans() }})
                        </p>
                    @endif
                </div>
            @endif

            {{-- Update Status --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider border-b border-gray-100 pb-3">
                    Ubah Status Pesanan
                </h3>
                <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="text-[11px] font-bold text-gray-500 block mb-1">Status Baru</label>
                        <select name="status" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending (Menunggu Pembayaran)</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing (Sedang Diproses)</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped (Dalam Pengiriman)</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed (Pesanan Selesai)</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled (Dibatalkan)</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs py-2.5 rounded-xl transition uppercase tracking-wider">
                        Simpan Status
                    </button>
                </form>
            </div>

            {{-- Input Resi --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider border-b border-gray-100 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast text-orange-600"></i>
                    <span>Input Nomor Resi</span>
                </h3>
                <form action="{{ route('admin.orders.update-tracking', $order->id) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="text-[11px] font-bold text-gray-500 block mb-1">Nama Kurir</label>
                        <input type="text" name="courier" value="{{ old('courier', $order->courier) }}"
                               placeholder="Contoh: JNE Reguler / J&T Express"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-gray-500 block mb-1">Nomor Resi (AWB)</label>
                        <input type="text" name="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}"
                               placeholder="Contoh: JNE123456789ID" required
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    </div>
                    <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs py-2.5 rounded-xl transition uppercase tracking-wider shadow-md shadow-orange-600/20">
                        Simpan & Update Resi
                    </button>
                </form>
            </div>

            {{-- Status pembayaran + konfirmasi lunas.

                 Rincian keuangannya sudah pindah ke panel Informasi Pembayaran
                 di kolom kiri; di sini tinggal yang perlu ditindaklanjuti. --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4 text-xs">
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider border-b border-gray-100 pb-3">
                    Status Pembayaran
                </h3>

                <div class="flex justify-between items-center">
                    <span class="text-gray-400 font-bold text-[10px] uppercase">Dibayar Pembeli:</span>
                    <span class="font-black text-sm text-orange-600">
                        Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                    </span>
                </div>

                <div class="border-t border-gray-100 pt-3 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 font-bold text-[10px] uppercase">Metode Bayar:</span>
                        <span class="font-bold text-slate-900 uppercase">{{ $order->payment_method }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 font-bold text-[10px] uppercase">Status Bayar:</span>
                        @if($order->payment_status === 'paid')
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300">
                                ✓ Lunas
                            </span>
                        @elseif($order->payment_status === 'pending_verification')
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300">
                                ⏳ Menunggu Verifikasi
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-red-100 text-red-800 border border-red-300">
                                ✗ Belum Bayar
                            </span>
                        @endif
                    </div>
                </div>

                @if($order->payment_status !== 'paid')
                    <form action="{{ route('admin.orders.confirm-payment', $order->id) }}" method="POST" class="pt-2">
                        @csrf
                        @method('PATCH')
                        <button type="submit" onclick="return confirm('Tandai pembayaran pesanan ini sebagai LUNAS?')"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2.5 rounded-xl transition uppercase tracking-wider shadow-sm">
                            <i class="fa-solid fa-check mr-1"></i> Tandai Lunas
                        </button>
                    </form>
                @endif
            </div>

        </div>
    </div>

</div>
@endsection

