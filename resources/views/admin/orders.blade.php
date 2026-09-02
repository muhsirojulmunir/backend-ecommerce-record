@extends('layouts.app')

@section('title', 'Pesanan Saya')
@section('page_title', 'Pesanan Saya')

@section('content')
<div class="space-y-4" x-data="orderBulk()">

    {{-- Flash Alert Success --}}
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

    {{-- Flash Alert Error --}}
    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-red-600 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.closest('div').remove()" class="text-red-500 hover:text-red-700 transition p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    {{-- ═══════════════ HEADER UTAMA PESANAN SAYA ═══════════════ --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Pesanan Saya</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola transaksi masuk, atur penjemputan ekspedisi Biteship, dan cetak label pengiriman.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="bukaEkspor = true"
                    class="bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs px-3.5 py-2 rounded-xl transition border border-gray-200 flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>Export</span>
            </button>

            <button type="button" @click="bukaRiwayat = true"
                    class="relative bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs px-3.5 py-2 rounded-xl transition border border-gray-200 flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-clock-rotate-left text-gray-500"></i>
                <span>Riwayat Download</span>
                @if($riwayatEkspor->count() > 0)
                    <span class="bg-[#EE4D2D] text-white text-[9px] font-black rounded-full px-1.5 py-0.2">
                        {{ $riwayatEkspor->count() }}
                    </span>
                @endif
            </button>

            <button type="button" @click="openBulkShipWithSelected()"
                    class="bg-[#EE4D2D] hover:bg-[#D73211] text-white font-bold text-xs px-4 py-2 rounded-xl transition shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-truck-fast"></i>
                <span>Atur Pengiriman</span>
                <span x-show="selected.length > 0" class="bg-white text-[#EE4D2D] text-[10px] px-1.5 py-0.5 rounded-full font-black" x-text="selected.length"></span>
            </button>

            <button type="button" @click="submitBulkPrint()"
                    class="bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs px-4 py-2 rounded-xl transition shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-print"></i>
                <span>Cetak Resi</span>
                <span x-show="selected.length > 0" class="bg-white text-slate-900 text-[10px] px-1.5 py-0.5 rounded-full font-black" x-text="selected.length"></span>
            </button>
        </div>
    </div>

    {{-- ═══════════════ SHOPEE STYLE STATUS TABS ═══════════════ --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        {{-- Top Level Tabs --}}
        <div class="flex items-center border-b border-gray-200 overflow-x-auto scrollbar-none px-4">
            @php
                $tabNow = request('tab', 'all');
                $shopeeTabs = [
                    'all'       => ['label' => 'Semua',                    'count' => $counts['all']],
                    'unpaid'    => ['label' => 'Belum Bayar',              'count' => $counts['unpaid']],
                    'ready'     => ['label' => 'Perlu Dikirim',            'count' => $counts['ready']],
                    'shipped'   => ['label' => 'Dikirim',                  'count' => $counts['shipped']],
                    'completed' => ['label' => 'Selesai',                  'count' => $counts['completed']],
                    'cancelled' => ['label' => 'Pengembalian/Pembatalan',   'count' => $counts['cancelled']],
                ];
            @endphp

            @foreach($shopeeTabs as $k => $t)
                @php
                    $isActive = ($tabNow === $k);
                    $url = route('admin.orders', array_merge(request()->except(['page']), ['tab' => $k]));
                @endphp
                <a href="{{ $url }}"
                   class="px-5 py-3.5 text-sm font-semibold whitespace-nowrap transition-colors flex items-center gap-1.5 border-b-2
                   {{ $isActive ? 'border-[#EE4D2D] text-[#EE4D2D] font-bold' : 'border-transparent text-gray-600 hover:text-slate-900' }}">
                    <span>{{ $t['label'] }}</span>
                    @if($t['count'] > 0 || $k !== 'all')
                        <span class="text-xs {{ $isActive ? 'text-[#EE4D2D]' : 'text-gray-400' }}">({{ $t['count'] }})</span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Sub-Filters (Tipe Pesanan & Sub-Status) --}}
        <div class="p-4 bg-gray-50/50 border-b border-gray-100 space-y-3">

            {{-- 1. Tipe Pesanan (Reguler / Instant Biteship) --}}
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="text-gray-500 font-medium w-24 shrink-0">Tipe Pesanan</span>
                @php
                    $shipType = request('shipping_type', 'all');
                    $shipTypes = [
                        'all'     => 'Semua',
                        'reguler' => 'Reguler (' . ($subCounts['reguler'] ?? 0) . ')',
                        'instant' => 'Instant / Same Day (' . ($subCounts['instant'] ?? 0) . ')',
                    ];
                @endphp
                @foreach($shipTypes as $stKey => $stLabel)
                    @php
                        $isStActive = ($shipType === $stKey);
                        $stUrl = route('admin.orders', array_merge(request()->except(['page']), ['shipping_type' => $stKey]));
                    @endphp
                    <a href="{{ $stUrl }}"
                       class="px-3.5 py-1 rounded-full text-xs font-semibold transition
                       {{ $isStActive ? 'border border-[#EE4D2D] text-[#EE4D2D] bg-[#FFF5F2]' : 'border border-gray-200 text-gray-600 bg-white hover:bg-gray-100' }}">
                        {{ $stLabel }}
                    </a>
                @endforeach
            </div>

            {{-- 2. Status Pesanan (Sub-status Perlu Diproses vs Telah Diproses) --}}
            @if(in_array($tabNow, ['all', 'ready']))
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-gray-500 font-medium w-24 shrink-0">Status Pesanan</span>
                    @php
                        $subStat = request('sub_status', 'all');
                        $subStats = [
                            'all'         => 'Semua',
                            'unprocessed' => 'Perlu diproses (' . ($subCounts['ready_unprocessed'] ?? 0) . ')',
                            'processed'   => 'Telah diproses (' . ($subCounts['ready_processed'] ?? 0) . ')',
                        ];
                    @endphp
                    @foreach($subStats as $ssKey => $ssLabel)
                        @php
                            $isSsActive = ($subStat === $ssKey);
                            $ssUrl = route('admin.orders', array_merge(request()->except(['page']), ['sub_status' => $ssKey]));
                        @endphp
                        <a href="{{ $ssUrl }}"
                           class="px-3.5 py-1 rounded-full text-xs font-semibold transition
                           {{ $isSsActive ? 'border border-[#EE4D2D] text-[#EE4D2D] bg-[#FFF5F2]' : 'border border-gray-200 text-gray-600 bg-white hover:bg-gray-100' }}">
                            {{ $ssLabel }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>


        {{-- ═══════════════ MULTI FILTER & SEARCH BAR (SHOPEE STYLE) ═══════════════ --}}
        <form action="{{ route('admin.orders') }}" method="GET" class="p-4 space-y-3">
            @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif
            @if(request('shipping_type')) <input type="hidden" name="shipping_type" value="{{ request('shipping_type') }}"> @endif
            @if(request('sub_status')) <input type="hidden" name="sub_status" value="{{ request('sub_status') }}"> @endif

            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                {{-- Search Box dengan Dropdown Selector --}}
                <div class="md:col-span-5 flex rounded-xl border border-gray-200 bg-white overflow-hidden focus-within:ring-2 focus-within:ring-[#EE4D2D]/20 focus-within:border-[#EE4D2D]">
                    <select name="search_type" class="bg-gray-50 text-xs font-semibold text-gray-700 border-r border-gray-200 px-3 py-2 focus:outline-none cursor-pointer">
                        <option value="order_number" {{ request('search_type') === 'order_number' ? 'selected' : '' }}>Order/Booking ID</option>
                        <option value="tracking_number" {{ request('search_type') === 'tracking_number' ? 'selected' : '' }}>No. Resi</option>
                        <option value="buyer" {{ request('search_type') === 'buyer' ? 'selected' : '' }}>Nama Pembeli</option>
                        <option value="product" {{ request('search_type') === 'product' ? 'selected' : '' }}>Nama Produk</option>
                        <option value="sku" {{ request('search_type') === 'sku' ? 'selected' : '' }}>Kode SKU</option>
                        <option value="all" {{ request('search_type', 'all') === 'all' ? 'selected' : '' }}>Semua Pencarian</option>
                    </select>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Ketik kata kunci pencarian..."
                           class="flex-1 px-3 py-2 text-xs border-0 focus:ring-0 focus:outline-none">
                </div>

                {{-- Jasa Kirim (Ekspedisi) --}}
                <div class="md:col-span-3">
                    <select name="courier" class="w-full text-xs rounded-xl border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-[#EE4D2D]/20 focus:border-[#EE4D2D] bg-white text-gray-700 font-medium">
                        <option value="all">Semua Jasa Kirim</option>
                        <option value="j&t" {{ request('courier') === 'j&t' ? 'selected' : '' }}>J&T Express</option>
                        <option value="sicepat" {{ request('courier') === 'sicepat' ? 'selected' : '' }}>SiCepat</option>
                        <option value="jne" {{ request('courier') === 'jne' ? 'selected' : '' }}>JNE</option>
                        <option value="pos" {{ request('courier') === 'pos' ? 'selected' : '' }}>Pos Indonesia</option>
                        <option value="anteraja" {{ request('courier') === 'anteraja' ? 'selected' : '' }}>AnterAja</option>
                        <option value="gojek" {{ request('courier') === 'gojek' ? 'selected' : '' }}>GoSend</option>
                        <option value="grab" {{ request('courier') === 'grab' ? 'selected' : '' }}>GrabExpress</option>
                    </select>
                </div>

                {{-- Filter Berdasarkan Hari / Tanggal --}}
                <div class="md:col-span-2" x-data="{ dateMode: '{{ request('date_filter', 'all') }}' }">
                    <select name="date_filter" x-model="dateMode" class="w-full text-xs rounded-xl border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-[#EE4D2D]/20 focus:border-[#EE4D2D] bg-white text-gray-700 font-medium">
                        <option value="all">Semua Waktu</option>
                        <option value="today">Hari Ini</option>
                        <option value="yesterday">Kemarin</option>
                        <option value="last_7_days">7 Hari Terakhir</option>
                        <option value="last_30_days">30 Hari Terakhir</option>
                        <option value="this_month">Bulan Ini</option>
                        <option value="custom">Pilih Tanggal...</option>
                    </select>
                </div>

                {{-- Tombol Terapkan & Atur Ulang --}}
                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 bg-[#EE4D2D] hover:bg-[#D73211] text-white text-xs font-bold py-2 px-3 rounded-xl transition shadow-sm text-center">
                        Terapkan
                    </button>
                    <a href="{{ route('admin.orders', ['tab' => request('tab', 'all')]) }}"
                       class="border border-gray-300 hover:bg-gray-100 text-gray-600 text-xs font-bold py-2 px-3 rounded-xl transition text-center">
                        Atur Ulang
                    </a>
                </div>
            </div>

            {{-- Custom Datepicker Range (Muncul jika pilih 'Pilih Tanggal...') --}}
            @if(request('date_filter') === 'custom')
                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                    <span class="text-xs text-gray-500 font-semibold">Rentang Tanggal:</span>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="text-xs rounded-lg border-gray-200 py-1.5 px-3">
                    <span class="text-xs text-gray-400">s/d</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="text-xs rounded-lg border-gray-200 py-1.5 px-3">
                    <button type="submit" class="text-xs font-bold text-[#EE4D2D] hover:underline">Terapkan Tanggal</button>
                </div>
            @endif
        </form>
    </div>

    {{-- ═══════════════ HASIL & PENGURUTAN (RESULTS BAR) ═══════════════ --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 px-1">
        <div class="flex items-center gap-2">
            <span class="text-sm font-black text-slate-800">{{ $orders->total() }} Results</span>
            <span class="text-xs text-gray-400">&bull; Menampilkan pesanan aktif</span>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            {{-- Sort Selector --}}
            <form action="{{ route('admin.orders') }}" method="GET" id="sortForm" class="flex items-center gap-2">
                @foreach(request()->except(['sort', 'page']) as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <span class="text-xs text-gray-500 font-medium hidden sm:inline">Urutkan:</span>
                <select name="sort" onchange="document.getElementById('sortForm').submit()"
                        class="text-xs font-semibold rounded-xl border border-gray-200 bg-white py-1.5 px-3 text-gray-700 focus:ring-2 focus:ring-[#EE4D2D]/20 focus:border-[#EE4D2D]">
                    <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Tanggal Pesanan (Terbaru ke Terlama)</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Tanggal Pesanan (Terlama ke Terbaru)</option>
                    <option value="amount_high" {{ request('sort') === 'amount_high' ? 'selected' : '' }}>Total Pesanan (Tertinggi)</option>
                    <option value="amount_low" {{ request('sort') === 'amount_low' ? 'selected' : '' }}>Total Pesanan (Terendah)</option>
                </select>
            </form>
        </div>
    </div>

    {{-- ═══════════════ FLOATING MASS ACTION BAR ═══════════════ --}}
    <div x-show="selected.length > 0" x-cloak
         class="sticky top-4 z-40 bg-slate-900/95 backdrop-blur text-white rounded-2xl p-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xl border border-slate-700">
        <div class="flex items-center gap-3">
            <span class="bg-[#EE4D2D] text-white font-black text-xs px-2.5 py-1 rounded-lg" x-text="selected.length"></span>
            <span class="font-bold text-xs">Pesanan Dipilih untuk Aksi Massal</span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="showBulkShipModal = true"
                    class="inline-flex items-center gap-1.5 bg-[#EE4D2D] hover:bg-[#D73211] text-white font-bold text-xs px-4 py-2 rounded-xl transition shadow-sm">
                <i class="fa-solid fa-truck-fast"></i> Atur Pengiriman (BiteShip)
            </button>
            <button type="button" @click="submitBulkPrint()"
                    class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition shadow-sm border border-slate-600">
                <i class="fa-solid fa-print"></i> Cetak Resi
            </button>
            @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('admin')))
                <button type="button" @click="konfirmasiHapusBulk()"
                        class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition shadow-sm cursor-pointer">
                    <i class="fa-solid fa-trash-can"></i> Hapus Pesanan (<span x-text="selected.length"></span>)
                </button>
            @endif
            <button type="button" @click="selected = []; selectAll = false"
                    class="text-gray-400 hover:text-white text-xs font-bold px-2.5 py-2 rounded-xl transition">
                Batal
            </button>
        </div>
    </div>

    {{-- ═══════════════ SHOPEE STYLE ORDER CARDS TABLE ═══════════════ --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        @if($orders->isEmpty())
            <div class="p-16 text-center">
                <i class="fa-regular fa-folder-open text-4xl text-gray-300 mb-3 block"></i>
                <h3 class="text-sm font-bold text-gray-600">Tidak ada pesanan yang sesuai</h3>
                <p class="text-xs text-gray-400 mt-1">Coba sesuaikan filter status, tipe pesanan, atau kata kunci pencarian Anda.</p>
                <a href="{{ route('admin.orders') }}" class="inline-block mt-4 text-xs font-bold text-[#EE4D2D] hover:underline">
                    Reset Semua Filter
                </a>
            </div>
        @else
            {{-- Table Column Headers (Shopee Seller Style) --}}
            <div class="hidden lg:grid grid-cols-12 gap-4 px-4 py-3 bg-gray-100/70 border-b border-gray-200 text-xs font-bold text-gray-600">
                <div class="col-span-4 flex items-center gap-3">
                    <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                           class="rounded border-gray-300 text-[#EE4D2D] focus:ring-[#EE4D2D] cursor-pointer w-4 h-4">
                    <span>Produk</span>
                </div>
                <div class="col-span-2 text-left">Dibayar Pembeli</div>
                <div class="col-span-2 text-left">Status / Batas Waktu</div>
                <div class="col-span-2 text-left">Jasa Kirim</div>
                <div class="col-span-2 text-right">Aksi</div>
            </div>

            {{-- List of Order Cards --}}
            <div class="divide-y-8 divide-gray-100">
                @foreach($orders as $order)
                    @php
                        $isPaid = $order->payment_status === 'paid';
                        $canProcess = $isPaid && !in_array($order->status, ['cancelled']);
                        $alamat = (array) $order->shipping_address;
                        $namaPenerima = $alamat['name'] ?? ($order->user->name ?? 'Pelanggan');
                    @endphp

                    <div class="bg-white transition hover:bg-gray-50/40"
                         :class="selected.some(id => String(id) === '{{ $order->id }}') && 'bg-orange-50/30'">

                        {{-- ── Card Top Strip (Header Pembeli & No. Pesanan) ── --}}
                        <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-2.5 bg-gray-50/80 border-b border-gray-200 text-xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                @if($canProcess)
                                    <input type="checkbox" :value="{{ $order->id }}" x-model="selected"
                                           class="rounded border-gray-300 text-[#EE4D2D] focus:ring-[#EE4D2D] cursor-pointer w-4 h-4 shrink-0"
                                           title="Pilih pesanan lunas ini untuk diproses">
                                @else
                                    <span class="inline-flex items-center justify-center w-4 h-4 shrink-0"
                                          title="{{ !$isPaid ? 'Pesanan Belum Lunas - Tidak dapat diproses pengiriman' : 'Pesanan Telah Dibatalkan' }}">
                                        <input type="checkbox" disabled
                                               class="rounded border-gray-200 text-gray-300 cursor-not-allowed w-4 h-4 bg-gray-100 opacity-40">
                                    </span>
                                @endif

                                {{-- Avatar & Nama Pembeli --}}
                                <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-[10px] shrink-0">
                                    {{ strtoupper(substr($namaPenerima, 0, 1)) }}
                                </div>
                                <span class="font-bold text-slate-800 truncate max-w-[140px] sm:max-w-[200px]" title="{{ $namaPenerima }}">
                                    {{ $namaPenerima }}
                                </span>

                                <span class="text-gray-300 mx-1 hidden sm:inline">|</span>
                                <span class="text-[11px] text-gray-400 hidden sm:inline shrink-0">
                                    {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                                </span>
                            </div>

                            {{-- No. Pesanan --}}
                            <div class="flex items-center gap-1.5 text-xs text-gray-500 shrink-0 ml-auto">
                                <span>No. Pesanan</span>
                                <a href="{{ route('admin.orders.show', $order->id) }}"
                                   class="font-mono font-bold text-slate-900 hover:text-[#EE4D2D] transition tracking-tight">
                                    {{ $order->order_number }}
                                </a>
                                <button type="button" @click="navigator.clipboard.writeText('{{ $order->order_number }}'); alert('Nomor pesanan disalin!')"
                                        class="text-gray-400 hover:text-gray-600 p-0.5" title="Salin No. Pesanan">
                                    <i class="fa-regular fa-copy text-[11px]"></i>
                                </button>
                            </div>
                        </div>

                        {{-- ── Card Grid Body ── --}}
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 p-4 items-start text-xs">
                            {{-- 1. Kolom Produk (col-span-4) --}}
                            <div class="lg:col-span-4 space-y-3">
                                @forelse($order->items as $item)
                                    <div class="flex items-start gap-3">
                                        <img src="{{ $item->product?->image_url ?? asset('images/no-image.png') }}"
                                             alt="{{ $item->product_name }}"
                                             class="w-14 h-14 rounded-md object-cover bg-gray-100 border border-gray-200 shrink-0"
                                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=120&q=80';">

                                        <div class="min-w-0 flex-1">
                                            <p class="font-bold text-slate-800 leading-snug line-clamp-2 hover:text-[#EE4D2D] transition">
                                                {{ $item->product_name }}
                                            </p>
                                            @if($item->variant_info)
                                                <p class="text-[11px] text-gray-500 mt-0.5">
                                                    Variasi: {{ $item->variant_info }}
                                                </p>
                                            @endif
                                            @if($item->productVariant?->sku)
                                                <p class="text-[10px] text-gray-400 font-mono">
                                                    SKU: {{ $item->productVariant->sku }}
                                                </p>
                                            @endif
                                        </div>

                                        <span class="text-xs text-gray-600 font-medium shrink-0">x{{ $item->quantity }}</span>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-400 italic">Tidak ada item pesanan.</p>
                                @endforelse
                            </div>

                            {{-- 2. Kolom Dibayar Pembeli (col-span-2) --}}
                            <div class="lg:col-span-2">
                                <p class="lg:hidden text-[10px] font-bold uppercase text-gray-400 mb-0.5">Dibayar Pembeli</p>
                                <p class="font-bold text-slate-900 text-sm">
                                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                </p>
                                <p class="text-[11px] text-gray-500 mt-0.5 font-medium">
                                    {{ $order->payment_method === 'COD' ? 'COD (Bayar di Tempat)' : 'Online Payment' }}
                                </p>
                                @if($isPaid)
                                    <span class="inline-block mt-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Lunas
                                    </span>
                                @else
                                    <span class="inline-block mt-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Belum Bayar
                                    </span>
                                @endif
                            </div>

                            {{-- 3. Kolom Status & Batas Waktu (col-span-2) --}}
                            <div class="lg:col-span-2">
                                <p class="lg:hidden text-[10px] font-bold uppercase text-gray-400 mb-0.5">Status</p>
                                <p class="font-bold text-slate-800 text-xs">
                                    @if(!$isPaid && $order->status !== 'cancelled')
                                        <span class="text-amber-600 font-extrabold flex items-center gap-1">
                                            <i class="fa-solid fa-hourglass-half text-[10px]"></i> Menunggu Pembayaran
                                        </span>
                                    @elseif($order->status === 'cancelled')
                                        <span class="text-gray-500 font-bold">Dibatalkan</span>
                                    @elseif($order->status === 'shipped')
                                        <span class="text-sky-600 font-bold">Dikirim</span>
                                    @elseif($order->status === 'completed')
                                        <span class="text-emerald-600 font-bold">Selesai</span>
                                    @elseif(in_array($order->status, ['pending', 'processing']))
                                        <span class="text-indigo-600 font-bold">Perlu Dikirim</span>
                                    @else
                                        {{ $order->status_label ?? ucfirst($order->status) }}
                                    @endif
                                </p>
                                <p class="text-[10px] text-gray-400 mt-1 leading-tight">
                                    @if($order->status === 'shipped')
                                        Paket dalam perjalanan kurir
                                    @elseif($order->status === 'cancelled')
                                        Pesanan telah dibatalkan
                                    @elseif($isPaid && $order->tracking_number && !str_starts_with($order->tracking_number, 'REC-'))
                                        Menunggu penjemputan / drop-off
                                    @elseif($isPaid)
                                        Menunggu pengiriman diverifikasi oleh Jasa Kirim.
                                    @else
                                        Menunggu pembayaran pembeli
                                    @endif
                                </p>

                                @php $retur = $order->returns->firstWhere('type', 'return'); @endphp
                                @if($retur)
                                    <a href="{{ route('admin.returns.show', $retur->id) }}"
                                       class="inline-block mt-1.5 text-[10px] font-bold text-rose-600 hover:underline">
                                        <i class="fa-solid fa-rotate-left mr-0.5"></i> {{ $retur->return_number ?? 'Pengembalian' }}
                                    </a>
                                @endif
                            </div>

                            {{-- 4. Kolom Jasa Kirim (col-span-2) --}}
                            <div class="lg:col-span-2">
                                <p class="lg:hidden text-[10px] font-bold uppercase text-gray-400 mb-0.5">Jasa Kirim</p>
                                <p class="font-bold text-slate-800 text-xs">
                                    {{ strtoupper($order->courier ?: 'Reguler') }}
                                </p>
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    {{ $order->shipping_service ?: 'Drop off / Pickup' }}
                                </p>
                                @if($order->tracking_number && !str_starts_with($order->tracking_number, 'REC-'))
                                    <p class="font-mono text-[10px] text-slate-600 font-bold mt-1 bg-gray-100 px-2 py-1 rounded inline-block">
                                        {{ $order->tracking_number }}
                                    </p>
                                @endif
                            </div>

                                                        {{-- 5. Kolom Aksi (col-span-2) --}}
                            <div class="lg:col-span-2 flex flex-col items-start lg:items-end justify-start gap-2 text-right shrink-0">
                                <a href="{{ route('admin.orders.show', $order->id) }}"
                                   class="text-[#0055AA] hover:text-[#EE4D2D] font-semibold text-xs transition whitespace-nowrap">
                                    Lihat Rincian Pengiriman
                                </a>

                                <a href="{{ route('admin.orders.show', $order->id) . '?print=1' }}" target="_blank"
                                   class="text-[#0055AA] hover:text-[#EE4D2D] font-semibold text-xs transition whitespace-nowrap">
                                    Cetak Label
                                </a>

                                @if($isPaid && $order->status !== 'shipped' && $order->status !== 'completed')
                                    <form action="{{ route('admin.orders.bulk-ship') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="order_ids[]" value="{{ $order->id }}">
                                        <button type="submit"
                                                onclick="return confirm('Atur penjemputan Biteship untuk pesanan #{{ $order->order_number }}?')"
                                                class="text-xs font-bold text-[#EE4D2D] hover:underline whitespace-nowrap">
                                            Atur Pengiriman
                                        </button>
                                    </form>
                                @endif

                                @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('admin')))
                                    <button type="button" @click.stop="$nextTick(() => konfirmasiHapusSingle({{ $order->id }}, '{{ $order->order_number }}'))"
                                            class="text-[11px] font-semibold text-rose-600 hover:text-rose-800 hover:underline inline-flex items-center gap-1.5 whitespace-nowrap">
                                        <i class="fa-regular fa-trash-can text-[10px]"></i>
                                        <span>Hapus Pesanan</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Shopee Style Pagination Footer --}}
            @if($orders->hasPages())
                <div class="p-4 bg-gray-50/70 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
                    <div>
                        Menampilkan <span class="font-bold text-slate-800">{{ $orders->firstItem() }}</span> - <span class="font-bold text-slate-800">{{ $orders->lastItem() }}</span> dari <span class="font-bold text-slate-800">{{ $orders->total() }}</span> pesanan
                    </div>
                    <div>
                        {{ $orders->links() }}
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- ══════════ Modal Export ══════════ --}}
    <div x-show="bukaEkspor" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-gray-100"
             @click.away="bukaEkspor = false">

            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-file-excel text-emerald-600"></i>
                    Export Pesanan
                </h3>
                <button type="button" @click="bukaEkspor = false" class="text-gray-400 hover:text-gray-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.orders.ekspor') }}" class="p-6 space-y-4">
                @csrf
                <p class="text-xs text-gray-500 leading-relaxed">
                    Pilih rentang tanggal pesanan dibuat. Kosongkan keduanya untuk mengekspor seluruh pesanan.
                </p>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Dari Tanggal</label>
                        <input type="date" name="dari" x-model="eksporDari"
                               class="w-full rounded-xl border-gray-200 text-xs focus:border-[#EE4D2D] focus:ring-[#EE4D2D]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Sampai Tanggal</label>
                        <input type="date" name="sampai" x-model="eksporSampai" :min="eksporDari"
                               class="w-full rounded-xl border-gray-200 text-xs focus:border-[#EE4D2D] focus:ring-[#EE4D2D]">
                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5">
                    <template x-for="p in pintasTanggal" :key="p.label">
                        <button type="button" @click="pakaiPintas(p)"
                                class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 text-[10px] font-bold transition"
                                x-text="p.label"></button>
                    </template>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="bukaEkspor = false"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-100 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-[#EE4D2D] hover:bg-[#D73211] text-white text-xs font-bold transition shadow-sm">
                        Export
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════ Modal Riwayat Download ══════════ --}}
    <div x-show="bukaRiwayat" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-gray-100 max-h-[85vh] flex flex-col"
             @click.away="bukaRiwayat = false">

            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-gray-500"></i>
                        Riwayat Download
                    </h3>
                    <p class="text-[10px] text-gray-400 mt-0.5">
                        Disimpan maksimal {{ \App\Models\OrderExport::BATAS_RIWAYAT }} berkas terbaru.
                    </p>
                </div>
                <button type="button" @click="bukaRiwayat = false" class="text-gray-400 hover:text-gray-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="overflow-y-auto p-4 space-y-2">
                @forelse($riwayatEkspor as $berkas)
                    <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-200 transition">
                        <span class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-file-excel"></i>
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-gray-800 truncate">{{ $berkas->file_name }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">
                                {{ $berkas->rentang }} &middot;
                                {{ number_format($berkas->jumlah_pesanan) }} pesanan,
                                {{ number_format($berkas->jumlah_baris) }} baris &middot;
                                {{ $berkas->ukuran_rapi }}
                            </p>
                            <p class="text-[10px] text-gray-400">
                                {{ $berkas->created_at->translatedFormat('d M Y, H:i') }}
                                @if($berkas->user) &middot; {{ $berkas->user->name }} @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            @if($berkas->berkas_ada)
                                <a href="{{ route('admin.orders.ekspor.unduh', $berkas->id) }}"
                                   class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[10px] font-bold transition">
                                    <i class="fa-solid fa-download mr-0.5"></i> Unduh
                                </a>
                            @else
                                <span class="px-2.5 py-1.5 rounded-lg bg-gray-100 text-gray-400 text-[10px] font-bold">
                                    Berkas hilang
                                </span>
                            @endif

                            <form method="POST" action="{{ route('admin.orders.ekspor.hapus', $berkas->id) }}"
                                  onsubmit="return confirm('Hapus berkas ekspor ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-2 py-1.5 rounded-lg text-gray-400 hover:text-rose-600 transition">
                                    <i class="fa-solid fa-trash-can text-[11px]"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <i class="fa-regular fa-folder-open text-3xl text-gray-200"></i>
                        <p class="mt-2 text-xs font-bold text-gray-500">Belum ada berkas ekspor</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ══════════ Modal Konfirmasi Pengiriman Massal ══════════ --}}
    <div x-show="showBulkShipModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-gray-100" @click.away="showBulkShipModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast text-[#EE4D2D]"></i>
                    Konfirmasi Pengiriman Massal (BiteShip)
                </h3>
                <button type="button" @click="showBulkShipModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p class="text-xs text-gray-600 leading-relaxed">
                Anda memilih <strong x-text="selected.length" class="text-[#EE4D2D]"></strong> pesanan. Pesanan yang <strong>SUDAH LUNAS</strong> akan otomatis dibuatkan pengiriman resmi via API BiteShip dan resi AWB akan terbit.
            </p>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-[11px] text-amber-800">
                <i class="fa-solid fa-circle-info mr-1"></i> Pesanan yang <strong>belum lunas</strong> akan dilewati secara otomatis oleh sistem.
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="showBulkShipModal = false"
                        class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-xl transition">
                    Batal
                </button>
                <form action="{{ route('admin.orders.bulk-ship') }}" method="POST" @submit="appendIds($event)">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 text-xs font-bold bg-[#EE4D2D] hover:bg-[#D73211] text-white rounded-xl transition shadow-sm">
                        Proses Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════ Modal Konfirmasi Hapus Pesanan (Super Admin) ══════════ --}}
    <div x-show="showDeleteModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-gray-100 transform transition-all"
             @click.away="showDeleteModal = false">
            
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl shrink-0 shadow-inner">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-slate-900 text-base">Hapus Pesanan Permanen?</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed" x-show="!isBulkDelete">
                        Anda akan menghapus pesanan <span class="font-mono font-bold text-slate-900" x-text="'#' + deleteTargetNumber"></span>. Seluruh data barang, resi, dan riwayat pesanan akan dihapus permanen.
                    </p>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed" x-show="isBulkDelete">
                        Anda akan menghapus <span class="font-bold text-rose-600" x-text="selected.length"></span> pesanan yang dipilih secara permanen dari sistem.
                    </p>
                </div>
            </div>

            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-3 text-[11px] text-rose-800 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-xs shrink-0"></i>
                <span><strong>Peringatan:</strong> Tindakan ini bersifat permanen dan tidak dapat dibatalkan.</span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" @click="showDeleteModal = false"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-100 transition">
                    Batal
                </button>

                {{-- Form Submit Hapus Single --}}
                <form x-show="!isBulkDelete" :action="deleteActionUrl" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white transition shadow-lg shadow-rose-600/30 flex items-center gap-2">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        <span>Ya, Hapus Sekarang</span>
                    </button>
                </form>

                {{-- Form Submit Hapus Bulk --}}
                <form x-show="isBulkDelete" action="{{ route('admin.orders.bulk-delete') }}" method="POST" @submit="appendIds($event)">
                    @csrf
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white transition shadow-lg shadow-rose-600/30 flex items-center gap-2">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        <span>Ya, Hapus Semua</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function orderBulk() {
    return {
        selected: [],
        selectAll: false,
        showBulkShipModal: false,

        // Modal Hapus Pesanan (Super Admin)
        showDeleteModal: false,
        deleteTargetId: null,
        deleteTargetNumber: '',
        deleteActionUrl: '',
        isBulkDelete: false,

        // Modal ekspor & riwayat unduhan
        bukaEkspor: false,
        bukaRiwayat: false,
        eksporDari: '',
        eksporSampai: '',

        pintasTanggal: [
            { label: '7 hari terakhir',  hari: 7 },
            { label: '30 hari terakhir', hari: 30 },
            { label: 'Bulan ini',        bulanIni: true },
            { label: 'Semua',            semua: true },
        ],

        pakaiPintas(p) {
            if (p.semua) {
                this.eksporDari = '';
                this.eksporSampai = '';
                return;
            }

            const tgl = (d) => d.toISOString().slice(0, 10);
            const kini = new Date();

            if (p.bulanIni) {
                this.eksporDari = tgl(new Date(kini.getFullYear(), kini.getMonth(), 1));
                this.eksporSampai = tgl(kini);
                return;
            }

            const mulai = new Date();
            mulai.setDate(mulai.getDate() - (p.hari - 1));
            this.eksporDari = tgl(mulai);
            this.eksporSampai = tgl(kini);
        },

        toggleAll() {
            if (this.selectAll) {
                this.selected = [
                    @foreach($orders as $order)
                        @if($order->payment_status === 'paid' && !in_array($order->status, ['cancelled']))
                            {{ $order->id }},
                        @endif
                    @endforeach
                ];
            } else {
                this.selected = [];
            }
        },

        openBulkShipWithSelected() {
            if (this.selected.length === 0) {
                alert('Pilih minimal satu pesanan terlebih dahulu.');
                return;
            }
            this.showBulkShipModal = true;
        },

        submitBulkPrint() {
            if (this.selected.length === 0) {
                alert('Pilih minimal satu pesanan untuk dicetak.');
                return;
            }
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('admin.orders.bulk-print') }}';
            form.target = '_blank';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            this.selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'order_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        },

        konfirmasiHapusSingle(id, number) {
            this.isBulkDelete = false;
            this.deleteTargetId = id;
            this.deleteTargetNumber = number;
            this.deleteActionUrl = '{{ url('admin/orders') }}/' + id;
            this.showDeleteModal = true;
        },

        submitBulkDelete() {
            this.konfirmasiHapusBulk();
        },

        konfirmasiHapusBulk() {
            if (!this.selected || this.selected.length === 0) {
                alert('Pilih minimal satu pesanan untuk dihapus.');
                return;
            }
            this.isBulkDelete = true;
            this.showDeleteModal = true;
        },

        appendIds(event) {
            const form = event.target;
            this.selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'order_ids[]';
                input.value = id;
                form.appendChild(input);
            });
        }
    }
}
</script>
@endsection
