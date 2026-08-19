@extends('layouts.app')

@section('title', 'Kelola Pesanan')
@section('page_title', 'Kelola Pesanan')

@section('content')
<div class="space-y-5" x-data="orderBulk()">

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
        <div class="flash-alert p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-red-600 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-red-500 hover:text-red-700 transition p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    {{-- Header Aksi Pengiriman & Cetak Resi --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-bold text-slate-800">Kelola Pengiriman Pesanan</h2>
            <p class="text-xs text-gray-400">Proses pengiriman otomatis via BiteShip & cetak label resi untuk pesanan yang sudah lunas.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            {{-- Ekspor & riwayat unduhan. Ditaruh bersama aksi lain di kepala
                 halaman supaya admin tidak perlu mencarinya di tempat lain. --}}
            <button type="button" @click="bukaEkspor = true"
                    class="bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs px-3.5 py-2 rounded-xl transition border border-gray-200 flex items-center gap-2">
                <i class="fa-solid fa-file-excel text-emerald-600 text-xs"></i>
                <span>Export</span>
            </button>

            <button type="button" @click="bukaRiwayat = true"
                    class="relative bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs px-3.5 py-2 rounded-xl transition border border-gray-200 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                <span>Riwayat Download</span>
                @if($riwayatEkspor->count() > 0)
                    <span class="absolute -top-1.5 -right-1.5 bg-orange-500 text-white text-[9px] font-black rounded-full w-4 h-4 flex items-center justify-center">
                        {{ $riwayatEkspor->count() }}
                    </span>
                @endif
            </button>

            <button type="button" @click="openBulkShipWithSelected()"
                    class="bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-truck-fast text-xs"></i>
                <span>Atur Pengiriman</span>
                <span x-show="selected.length > 0" class="bg-white text-orange-600 text-[10px] px-1.5 py-0.5 rounded-full font-black" x-text="selected.length"></span>
            </button>
            <button type="button" @click="submitBulkPrint()"
                    class="bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-print text-xs"></i>
                <span>Cetak Resi</span>
                <span x-show="selected.length > 0" class="bg-white text-slate-900 text-[10px] px-1.5 py-0.5 rounded-full font-black" x-text="selected.length"></span>
            </button>
        </div>
    </div>

    {{-- Filter Tabs + Search --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        {{-- Status Tabs Rapi & Tanpa Icon Berlebihan --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-2 md:pb-0 flex-wrap">
            @php
                $currentTab = request('tab', 'all');
                $tabs = [
                    'all'       => ['label' => 'Semua Pesanan',        'count' => $counts['all'],       'active' => 'bg-slate-900 text-white'],
                    'ready'     => ['label' => 'Siap Diproses (Lunas)', 'count' => $counts['ready'],     'active' => 'bg-emerald-600 text-white'],
                    'unpaid'    => ['label' => 'Belum Bayar',           'count' => $counts['unpaid'],    'active' => 'bg-amber-600 text-white'],
                    'shipped'   => ['label' => 'Dikirim',               'count' => $counts['shipped'],   'active' => 'bg-blue-600 text-white'],
                    'completed' => ['label' => 'Selesai',               'count' => $counts['completed'], 'active' => 'bg-teal-600 text-white'],
                    'cancelled' => ['label' => 'Batal',                 'count' => $counts['cancelled'], 'active' => 'bg-gray-600 text-white'],
                ];
            @endphp

            @foreach($tabs as $key => $tab)
                <a href="{{ route('admin.orders', array_merge(request()->except(['page','payment_status','status']), ['tab' => $key])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 shrink-0
                   {{ $currentTab === $key ? $tab['active'] . ' shadow-sm' : 'text-gray-600 hover:bg-gray-100 bg-gray-50' }}">
                    <span>{{ $tab['label'] }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ $currentTab === $key ? 'bg-white/20' : 'bg-gray-200 text-gray-700' }}">{{ $tab['count'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Search --}}
        <form action="{{ route('admin.orders') }}" method="GET" class="flex items-center gap-2 shrink-0">
            @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari No. Pesanan / Resi / Nama..."
                       class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 w-64">
            </div>
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold px-4 py-2 rounded-xl transition">Cari</button>
        </form>
    </div>

    {{-- ── Floating Action Bar Saat Pesanan Dicentang ── --}}
    <div x-show="selected.length > 0" x-cloak
         class="sticky top-4 z-40 bg-slate-900/95 backdrop-blur text-white rounded-2xl p-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xl border border-slate-700">
        <div class="flex items-center gap-3">
            <span class="bg-orange-500 text-white font-black text-xs px-2.5 py-1 rounded-lg" x-text="selected.length"></span>
            <span class="font-bold text-xs">Pesanan Dicentang untuk Aksi Massal</span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="showBulkShipModal = true"
                    class="inline-flex items-center gap-1.5 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition shadow-sm">
                <i class="fa-solid fa-truck-fast"></i> Atur Pengiriman (BiteShip)
            </button>
            <button type="button" @click="submitBulkPrint()"
                    class="inline-flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs px-3.5 py-2 rounded-xl transition shadow-sm border border-slate-600">
                <i class="fa-solid fa-print"></i> Cetak Resi
            </button>
            <button type="button" @click="selected = []; selectAll = false"
                    class="text-gray-400 hover:text-white text-xs font-bold px-2.5 py-2 rounded-xl transition">
                Batal
            </button>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        @if($orders->isEmpty())
            <div class="p-16 text-center">
                <i class="fa-regular fa-folder-open text-4xl text-gray-300 mb-3 block"></i>
                <h3 class="text-sm font-bold text-gray-500">Tidak ada data pesanan</h3>
                <p class="text-xs text-gray-400 mt-1">Belum ada pesanan yang sesuai dengan filter yang dipilih.</p>
            </div>
        @else
            {{-- ── Kepala kolom ── --}}
            <div class="hidden lg:flex items-center gap-4 px-4 py-3 bg-gray-50/70 border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                <div class="w-5 shrink-0">
                    <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                           class="rounded border-gray-300 text-orange-500 focus:ring-orange-500 cursor-pointer w-4 h-4">
                </div>
                <div class="flex-1 min-w-0">Produk</div>
                <div class="w-40 shrink-0">Dibayar Pembeli</div>
                <div class="w-36 shrink-0">Status</div>
                <div class="w-40 shrink-0">Jasa Kirim</div>
                <div class="w-40 shrink-0 text-right">Aksi</div>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach($orders as $order)
                    @php $isPaid = $order->payment_status === 'paid'; @endphp

                    <div class="transition {{ !$isPaid ? 'bg-amber-50/20' : '' }}"
                         {{-- Dibandingkan sebagai teks lewat String(). --}}
                         :class="selected.some(id => String(id) === '{{ $order->id }}') && 'bg-orange-50/40'">

                        {{-- ── Kepala kartu: pembeli di kiri, nomor pesanan di kanan ── --}}
                        <div class="flex items-center gap-3 px-4 py-2.5 bg-gray-50/60 border-b border-gray-100">
                            <input type="checkbox" :value="{{ $order->id }}" x-model="selected"
                                   class="rounded border-gray-300 text-orange-500 focus:ring-orange-500 cursor-pointer w-4 h-4 shrink-0">

                            <span class="w-6 h-6 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-user text-[9px]"></i>
                            </span>

                            <span class="font-bold text-gray-800 text-xs truncate">
                                {{ $order->user->name ?? 'Guest' }}
                            </span>

                            <span class="text-[10px] text-gray-400 hidden sm:inline shrink-0">
                                {{ $order->created_at->format('d M Y, H:i') }}
                            </span>

                            <span class="ml-auto text-[11px] text-gray-500 shrink-0">
                                No. Pesanan
                                <a href="{{ route('admin.orders.show', $order->id) }}"
                                   class="font-mono font-bold text-slate-900 hover:text-orange-600 transition">
                                    {{ $order->order_number }}
                                </a>
                            </span>
                        </div>

                        {{-- ── Isi kartu ── --}}
                        <div class="flex flex-col lg:flex-row lg:items-start gap-4 px-4 py-4">

                            {{-- Kolom kiri: daftar produk. Diberi ruang tumbuh
                                 supaya kolom lain tetap sejajar antar kartu. --}}
                            <div class="hidden lg:block w-5 shrink-0"></div>

                            <div class="flex-1 min-w-0 space-y-3">
                                {{-- Pesanan lama pada data uji ada yang barisnya --}}
                                @if($order->items->isEmpty())
                                    <p class="text-[11px] text-gray-400 italic">
                                        Tidak ada rincian barang pada pesanan ini.
                                    </p>
                                @endif

                                @foreach($order->items as $item)
                                    <div class="flex items-start gap-3">
                                        <img src="{{ $item->product?->image_url ?? asset('images/no-image.png') }}"
                                             alt="{{ $item->product_name }}"
                                             class="w-12 h-12 rounded-lg object-cover bg-gray-100 border border-gray-200 shrink-0"
                                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=120&q=80';">

                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs text-gray-800 leading-snug line-clamp-2">
                                                {{ $item->product_name }}
                                            </p>
                                            @if($item->variant_info)
                                                <p class="text-[10px] text-gray-400 mt-0.5 truncate">
                                                    Variasi: {{ $item->variant_info }}
                                                </p>
                                            @endif
                                        </div>

                                        <span class="text-[11px] text-gray-500 shrink-0">x{{ $item->quantity }}</span>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Dibayar Pembeli --}}
                            <div class="lg:w-40 shrink-0">
                                <p class="lg:hidden text-[10px] font-bold uppercase text-gray-400 mb-0.5">Dibayar Pembeli</p>
                                <p class="font-bold text-slate-900 text-xs">
                                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                </p>
                                <p class="text-[10px] text-gray-500 mt-0.5 uppercase">{{ $order->payment_method }}</p>
                                @if($isPaid)
                                    <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">Lunas</span>
                                @else
                                    <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-amber-100 text-amber-800 border border-amber-200">Belum Bayar</span>
                                @endif
                            </div>

                            {{-- Status --}}
                            <div class="lg:w-36 shrink-0">
                                <p class="lg:hidden text-[10px] font-bold uppercase text-gray-400 mb-0.5">Status</p>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider inline-flex items-center gap-1
                                    @if($order->status === 'pending') bg-amber-50 text-amber-800 border border-amber-200
                                    @elseif($order->status === 'processing') bg-blue-50 text-blue-800 border border-blue-200
                                    @elseif($order->status === 'shipped') bg-indigo-50 text-indigo-800 border border-indigo-200
                                    @elseif($order->status === 'completed') bg-emerald-50 text-emerald-800 border border-emerald-200
                                    @else bg-rose-50 text-rose-800 border border-rose-200
                                    @endif">
                                    <i class="fa-solid fa-circle text-[5px]"></i>
                                    {{ $order->status_label }}
                                </span>

                                {{-- Nomor pengembalian ikut tampil bila ada, --}}
                                @php $retur = $order->returns->firstWhere('type', 'return'); @endphp
                                @if($retur)
                                    <a href="{{ route('admin.returns.show', $retur->id) }}"
                                       class="block mt-1.5 text-[10px] font-bold text-rose-600 hover:text-rose-700">
                                        <i class="fa-solid fa-rotate-left mr-0.5"></i>
                                        {{ $retur->return_number ?? 'Pengembalian' }}
                                    </a>
                                @endif
                            </div>

                            {{-- Jasa Kirim --}}
                            <div class="lg:w-40 shrink-0 min-w-0">
                                <p class="lg:hidden text-[10px] font-bold uppercase text-gray-400 mb-0.5">Jasa Kirim</p>
                                <p class="text-xs font-bold text-gray-800 truncate">{{ $order->courier ?: '—' }}</p>
                                @if($order->tracking_number)
                                    <p class="font-mono text-[10px] text-gray-500 mt-0.5 select-all break-all">
                                        {{ $order->tracking_number }}
                                    </p>
                                @else
                                    <p class="text-[10px] text-gray-400 italic mt-0.5">Belum ada resi</p>
                                @endif
                            </div>

                            {{-- Aksi --}}
                            <div class="lg:w-40 shrink-0">
                                <div class="flex lg:flex-col items-stretch gap-1.5 flex-wrap">
                                    @if($isPaid && $order->status !== 'shipped')
                                        <form action="{{ route('admin.orders.bulk-ship') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="order_ids[]" value="{{ $order->id }}">
                                            <button type="submit"
                                                    onclick="return confirm('Proses pengiriman BiteShip untuk pesanan #{{ $order->order_number }}?')"
                                                    class="w-full inline-flex items-center justify-center gap-1 bg-orange-600 hover:bg-orange-700 text-white font-bold text-[10px] px-2.5 py-1.5 rounded-lg transition shadow-sm">
                                                <i class="fa-solid fa-truck-fast"></i> Proses Pengiriman
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.orders.show', $order->id) . '?print=1' }}" target="_blank"
                                       class="inline-flex items-center justify-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-[10px] px-2.5 py-1.5 rounded-lg transition">
                                        <i class="fa-solid fa-print"></i> Cetak Label
                                    </a>

                                    <a href="{{ route('admin.orders.show', $order->id) }}"
                                       class="inline-flex items-center justify-center gap-1 bg-slate-800 hover:bg-slate-900 text-white font-bold text-[10px] px-2.5 py-1.5 rounded-lg transition">
                                        <i class="fa-solid fa-pen-to-square text-[9px]"></i> Lihat Rincian
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($orders->hasPages())
                <div class="p-4 border-t border-gray-100">{{ $orders->links() }}</div>
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
                    Pilih rentang tanggal pesanan dibuat. Kosongkan keduanya untuk
                    mengekspor seluruh pesanan.
                </p>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Dari Tanggal</label>
                        <input type="date" name="dari" x-model="eksporDari"
                               class="w-full rounded-xl border-gray-200 text-xs focus:border-orange-500 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Sampai Tanggal</label>
                        <input type="date" name="sampai" x-model="eksporSampai" :min="eksporDari"
                               class="w-full rounded-xl border-gray-200 text-xs focus:border-orange-500 focus:ring-orange-500">
                    </div>
                </div>

                {{-- Jalan pintas rentang yang paling sering dipakai. --}}
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="p in pintasTanggal" :key="p.label">
                        <button type="button" @click="pakaiPintas(p)"
                                class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 text-[10px] font-bold transition"
                                x-text="p.label"></button>
                    </template>
                </div>

                @error('sampai')
                    <p class="text-[11px] text-rose-600">{{ $message }}</p>
                @enderror

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="bukaEkspor = false"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-100 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold transition shadow-sm">
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
                        Disimpan maksimal {{ \App\Models\OrderExport::BATAS_RIWAYAT }} berkas terbaru —
                        yang lebih lama terhapus otomatis.
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
                                {{-- Barisnya tetap ditampilkan meski berkasnya --}}
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
                        <p class="text-[10px] text-gray-400 mt-0.5">Berkas yang kamu buat lewat tombol Export muncul di sini.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Pengiriman Massal --}}
    <div x-show="showBulkShipModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-gray-100" @click.away="showBulkShipModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast text-orange-500"></i>
                    Konfirmasi Pengiriman Massal (BiteShip)
                </h3>
                <button type="button" @click="showBulkShipModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p class="text-xs text-gray-600 leading-relaxed">
                Anda memilih <strong x-text="selected.length" class="text-orange-600"></strong> pesanan. Pesanan yang <strong>SUDAH LUNAS</strong> akan otomatis dibuatkan pengiriman resmi via API BiteShip dan resi AWB akan terbit.
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
                            class="px-4 py-2 text-xs font-bold bg-orange-600 hover:bg-orange-700 text-white rounded-xl transition shadow-sm">
                        Proses Sekarang
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

        // Modal ekspor & riwayat unduhan
        bukaEkspor: false,
        bukaRiwayat: false,
        eksporDari: '',
        eksporSampai: '',

        // Pintasan rentang yang paling sering diminta.
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
                        {{ $order->id }},
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
