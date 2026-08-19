@extends('layouts.app')

@section('title', 'Kelola Laporan')
@section('page_title', 'Kelola Laporan')
@section('page_subtitle', 'Analisa penjualan, produk terlaris, dan performa toko.')

@section('content')
@php
    // Data grafik dikirim ke Alpine supaya tooltip & pergantian metrik jalan tanpa reload
    $chartMaxRevenue = max(1, collect($chart)->max('revenue'));
    $chartMaxOrders  = max(1, collect($chart)->max('orders'));

    $presets = [
        'today'      => 'Hari Ini',
        '7d'         => '7 Hari',
        '30d'        => '30 Hari',
        'this_month' => 'Bulan Ini',
        'last_month' => 'Bulan Lalu',
        'this_year'  => 'Tahun Ini',
    ];
@endphp

<div class="space-y-6"
     x-data="{
        metric: 'revenue',
        hover: null,
        showCustom: {{ $preset === 'custom' ? 'true' : 'false' }},
        rows: @js($chart),
        maxRevenue: {{ $chartMaxRevenue }},
        maxOrders: {{ $chartMaxOrders }},
        barHeight(row) {
            const max = this.metric === 'revenue' ? this.maxRevenue : this.maxOrders;
            const val = this.metric === 'revenue' ? row.revenue : row.orders;
            return max > 0 ? Math.max(val > 0 ? 2 : 0, (val / max) * 100) : 0;
        },
        rupiah(n) {
            return 'Rp ' + Math.round(n).toLocaleString('id-ID');
        }
     }">

    {{-- ── Filter Rentang Tanggal ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-4">
        <div class="flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4">

            {{-- Preset --}}
            <div class="flex items-center gap-1.5 flex-wrap">
                @foreach($presets as $key => $label)
                    <a href="{{ route('admin.reports', ['preset' => $key]) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0
                       {{ $preset === $key ? 'bg-slate-900 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach

                <button type="button" @click="showCustom = !showCustom"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition shrink-0 flex items-center gap-1.5
                        {{ $preset === 'custom' ? 'bg-orange-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 bg-gray-50' }}">
                    <i class="fa-solid fa-calendar-days text-[10px]"></i>
                    Pilih Tanggal
                </button>
            </div>

            {{-- Info rentang + export --}}
            <div class="flex items-center gap-3 shrink-0">
                <div class="text-right hidden sm:block">
                    <p class="text-[10px] text-gray-400 font-bold uppercase">Periode Laporan</p>
                    <p class="text-xs font-black text-gray-700">
                        {{ $from->translatedFormat('d M Y') }} — {{ $to->translatedFormat('d M Y') }}
                    </p>
                </div>
                <a href="{{ route('admin.reports.export', request()->query()) }}"
                   class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition flex items-center gap-2">
                    <i class="fa-solid fa-file-csv"></i>
                    <span class="hidden sm:inline">Export CSV</span>
                </a>
            </div>
        </div>

        {{-- Form rentang kustom --}}
        <form x-show="showCustom" x-transition x-cloak action="{{ route('admin.reports') }}" method="GET"
              class="flex flex-wrap items-end gap-3 pt-4 border-t border-gray-100">
            <input type="hidden" name="preset" value="custom">
            <div>
                <label class="block text-[10px] font-black text-gray-500 uppercase mb-1.5">Dari Tanggal</label>
                <input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}"
                       class="border border-gray-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-500 uppercase mb-1.5">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}"
                       class="border border-gray-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
            </div>
            <button type="submit"
                    class="bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold px-5 py-2.5 rounded-xl transition shadow">
                Terapkan
            </button>
        </form>
    </div>

    {{-- ── Kartu Ringkasan ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        {{-- Omzet --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-rupiah-sign"></i>
                </div>
                @if($growth['revenue'] !== null)
                    <span class="text-[10px] font-black px-2 py-1 rounded-lg {{ $growth['revenue'] >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                        <i class="fa-solid {{ $growth['revenue'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} mr-0.5"></i>
                        {{ number_format(abs($growth['revenue']), 1) }}%
                    </span>
                @endif
            </div>
            <p class="text-xs font-semibold text-gray-400">Total Omzet</p>
            <h3 class="text-xl font-black text-gray-800 mt-1">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</h3>
            <p class="text-[10px] text-gray-400 mt-1">
                Periode sebelumnya: Rp {{ number_format($previous['revenue'], 0, ',', '.') }}
            </p>
        </div>

        {{-- Pesanan --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                @if($growth['orders'] !== null)
                    <span class="text-[10px] font-black px-2 py-1 rounded-lg {{ $growth['orders'] >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                        <i class="fa-solid {{ $growth['orders'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} mr-0.5"></i>
                        {{ number_format(abs($growth['orders']), 1) }}%
                    </span>
                @endif
            </div>
            <p class="text-xs font-semibold text-gray-400">Jumlah Pesanan</p>
            <h3 class="text-xl font-black text-gray-800 mt-1">{{ number_format($summary['orders']) }}</h3>
            <p class="text-[10px] text-gray-400 mt-1">{{ number_format($summary['paid_orders']) }} pesanan lunas</p>
        </div>

        {{-- Produk terjual --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <p class="text-xs font-semibold text-gray-400">Produk Terjual</p>
            <h3 class="text-xl font-black text-gray-800 mt-1">{{ number_format($summary['items_sold']) }}</h3>
            <p class="text-[10px] text-gray-400 mt-1">total unit dari pesanan lunas</p>
        </div>

        {{-- Rata-rata --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg mb-3">
                <i class="fa-solid fa-chart-simple"></i>
            </div>
            <p class="text-xs font-semibold text-gray-400">Rata-rata / Pesanan</p>
            <h3 class="text-xl font-black text-gray-800 mt-1">Rp {{ number_format($summary['avg_order'], 0, ',', '.') }}</h3>
            <p class="text-[10px] text-gray-400 mt-1">{{ number_format($summary['new_customers']) }} customer baru periode ini</p>
        </div>
    </div>

    {{-- ── Grafik Penjualan Interaktif ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <h4 class="text-sm font-black text-gray-800 uppercase tracking-wide">Tren Penjualan</h4>
                <p class="text-[11px] text-gray-400 mt-0.5">Arahkan kursor ke batang untuk melihat detail per tanggal.</p>
            </div>

            {{-- Pergantian metrik --}}
            <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl shrink-0">
                <button type="button" @click="metric = 'revenue'"
                        class="px-4 py-1.5 rounded-lg text-xs font-bold transition"
                        :class="metric === 'revenue' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                    Omzet
                </button>
                <button type="button" @click="metric = 'orders'"
                        class="px-4 py-1.5 rounded-lg text-xs font-bold transition"
                        :class="metric === 'orders' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                    Pesanan
                </button>
            </div>
        </div>

        @if(empty($chart))
            <div class="py-16 text-center">
                <i class="fa-solid fa-chart-column text-4xl text-gray-200 mb-3 block"></i>
                <p class="text-sm font-bold text-gray-400">Belum ada data pada periode ini.</p>
            </div>
        @else
            <div class="relative">
                {{-- Tooltip melayang --}}
                <div x-show="hover !== null" x-cloak x-transition.opacity
                     class="absolute -top-2 left-1/2 -translate-x-1/2 z-20 bg-slate-900 text-white rounded-xl px-4 py-2.5 shadow-xl pointer-events-none">
                    <p class="text-[10px] font-bold text-slate-400 uppercase" x-text="hover?.label"></p>
                    <p class="text-sm font-black" x-text="rupiah(hover?.revenue ?? 0)"></p>
                    <p class="text-[10px] text-slate-300">
                        <span x-text="hover?.orders ?? 0"></span> pesanan ·
                        <span x-text="hover?.paid_orders ?? 0"></span> lunas
                    </p>
                </div>

                {{-- Batang grafik --}}
                <div class="flex items-end gap-1 h-56 border-b border-l border-gray-100 pt-8 px-1 overflow-x-auto">
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="flex-1 min-w-[10px] h-full flex flex-col justify-end items-center group cursor-pointer"
                             @mouseenter="hover = row" @mouseleave="hover = null">
                            <div class="w-full rounded-t-md transition-all duration-300 bg-gradient-to-t from-orange-500 to-amber-400 group-hover:from-orange-600 group-hover:to-amber-500"
                                 :style="'height: ' + barHeight(row) + '%'"
                                 :class="hover === row ? 'ring-2 ring-orange-300' : ''"></div>
                        </div>
                    </template>
                </div>

                {{-- Label sumbu X (dijarangkan supaya tidak menumpuk) --}}
                <div class="flex gap-1 px-1 mt-2 overflow-x-auto">
                    <template x-for="(row, i) in rows" :key="'l' + i">
                        <div class="flex-1 min-w-[10px] text-center">
                            <span class="text-[9px] text-gray-400 font-semibold whitespace-nowrap"
                                  x-show="rows.length <= 15 || i % Math.ceil(rows.length / 10) === 0"
                                  x-text="row.label"></span>
                        </div>
                    </template>
                </div>
            </div>
        @endif
    </div>

    {{-- ── Status Pesanan & Metode Pembayaran ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Sebaran status --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h4 class="text-sm font-black text-gray-800 uppercase tracking-wide mb-5">Status Pesanan Periode Ini</h4>

            @php
                $statusMeta = [
                    'pending'    => ['Menunggu',   'bg-gray-400',    'text-gray-700',    'bg-gray-50'],
                    'processing' => ['Diproses',   'bg-amber-500',   'text-amber-700',   'bg-amber-50'],
                    'shipped'    => ['Dikirim',    'bg-blue-500',    'text-blue-700',    'bg-blue-50'],
                    'completed'  => ['Selesai',    'bg-emerald-500', 'text-emerald-700', 'bg-emerald-50'],
                    'cancelled'  => ['Dibatalkan', 'bg-rose-500',    'text-rose-700',    'bg-rose-50'],
                ];
                $statusTotal = max(1, array_sum($statusCounts));
            @endphp

            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
                @foreach($statusMeta as $key => [$label, $bar, $text, $bg])
                    <div class="p-4 rounded-xl {{ $bg }} border border-gray-100 text-center">
                        <p class="text-[10px] {{ $text }} font-black uppercase">{{ $label }}</p>
                        <p class="text-xl font-black {{ $text }} mt-1.5">{{ $statusCounts[$key] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Bar proporsi --}}
            <div class="flex h-2.5 rounded-full overflow-hidden bg-gray-100">
                @foreach($statusMeta as $key => [$label, $bar, $text, $bg])
                    @if($statusCounts[$key] > 0)
                        <div class="{{ $bar }} transition-all duration-500"
                             style="width: {{ $statusCounts[$key] / $statusTotal * 100 }}%"
                             title="{{ $label }}: {{ $statusCounts[$key] }}"></div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Metode pembayaran --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h4 class="text-sm font-black text-gray-800 uppercase tracking-wide mb-5">Metode Pembayaran</h4>

            @php $payTotal = max(1, $paymentMix->sum('revenue')); @endphp

            @forelse($paymentMix as $pay)
                <div class="mb-4 last:mb-0">
                    <div class="flex justify-between items-baseline mb-1.5">
                        <span class="text-xs font-bold text-gray-700">{{ $pay->payment_method }}</span>
                        <span class="text-[10px] font-black text-gray-500">{{ round($pay->revenue / $payTotal * 100) }}%</span>
                    </div>
                    <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-orange-500 to-amber-400 rounded-full transition-all duration-500"
                             style="width: {{ $pay->revenue / $payTotal * 100 }}%"></div>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">
                        {{ $pay->total }} pesanan · Rp {{ number_format($pay->revenue, 0, ',', '.') }}
                    </p>
                </div>
            @empty
                <p class="text-xs text-gray-400 text-center py-8">Belum ada pembayaran lunas pada periode ini.</p>
            @endforelse
        </div>
    </div>

    {{-- ── Produk Terlaris & Kategori ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Produk terlaris --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h4 class="text-sm font-black text-gray-800 uppercase tracking-wide">Produk Terlaris</h4>
            </div>

            @if($topProducts->isEmpty())
                <p class="text-xs text-gray-400 text-center py-12">Belum ada produk terjual pada periode ini.</p>
            @else
                @php $topQty = max(1, $topProducts->max('qty')); @endphp
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-500">
                        <thead class="text-xs text-gray-400 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-bold w-10">#</th>
                                <th class="px-4 py-3 font-bold">Produk</th>
                                <th class="px-4 py-3 font-bold text-center">Terjual</th>
                                <th class="px-6 py-3 font-bold text-right">Omzet</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($topProducts as $i => $p)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-6 py-3.5">
                                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-black
                                            {{ $i < 3 ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $i + 1 }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="text-xs font-bold text-gray-800 truncate max-w-xs">{{ $p->product_name }}</p>
                                        <div class="h-1 rounded-full bg-gray-100 overflow-hidden mt-1.5 max-w-xs">
                                            <div class="h-full bg-orange-400 rounded-full" style="width: {{ $p->qty / $topQty * 100 }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-black">{{ $p->qty }}</span>
                                    </td>
                                    <td class="px-6 py-3.5 text-right font-bold text-gray-800 text-xs">
                                        Rp {{ number_format($p->revenue, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Kategori terlaris --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h4 class="text-sm font-black text-gray-800 uppercase tracking-wide mb-5">Kategori Teratas</h4>

            @php $catTotal = max(1, $topCategories->sum('revenue')); @endphp

            @forelse($topCategories as $cat)
                <div class="mb-4 last:mb-0">
                    <div class="flex justify-between items-baseline mb-1.5">
                        <span class="text-xs font-bold text-gray-700 truncate">{{ $cat->name }}</span>
                        <span class="text-[10px] font-black text-gray-500">{{ $cat->qty }} unit</span>
                    </div>
                    <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-indigo-500 to-blue-400 rounded-full transition-all duration-500"
                             style="width: {{ $cat->revenue / $catTotal * 100 }}%"></div>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">Rp {{ number_format($cat->revenue, 0, ',', '.') }}</p>
                </div>
            @empty
                <p class="text-xs text-gray-400 text-center py-8">Belum ada data kategori.</p>
            @endforelse
        </div>
    </div>

    {{-- ── Pelanggan Teratas ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h4 class="text-sm font-black text-gray-800 uppercase tracking-wide">Pelanggan dengan Belanja Terbesar</h4>
            @can('manage customers')
                <a href="{{ route('admin.customers', ['sort' => 'spend_desc']) }}"
                   class="text-xs font-bold text-orange-500 hover:text-orange-600 transition">Kelola Customer</a>
            @endcan
        </div>

        @if($topCustomers->isEmpty())
            <p class="text-xs text-gray-400 text-center py-12">Belum ada transaksi lunas pada periode ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500">
                    <thead class="text-xs text-gray-400 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 font-bold">Customer</th>
                            <th class="px-4 py-3 font-bold text-center">Pesanan</th>
                            <th class="px-4 py-3 font-bold text-right">Total Belanja</th>
                            <th class="px-6 py-3 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topCustomers as $i => $cust)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-500 to-rose-500 flex items-center justify-center font-bold text-white text-xs shrink-0">
                                            {{ strtoupper(substr($cust->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-gray-800 truncate">{{ $cust->name }}</p>
                                            <p class="text-[10px] text-gray-400 truncate">{{ $cust->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center text-xs font-black text-gray-700">{{ $cust->orders_count }}</td>
                                <td class="px-4 py-3.5 text-right font-bold text-emerald-600 text-xs">
                                    Rp {{ number_format($cust->revenue, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    @can('manage customers')
                                        <a href="{{ route('admin.customers.show', $cust->id) }}"
                                           class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                            Detail
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
