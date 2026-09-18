@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('page_title', 'Log Aktivitas')
@section('page_subtitle', 'Riwayat perubahan data yang dilakukan di Seller Center.')

@section('content')
@php
    use App\Support\DeviceDetector;
    // Warna & ikon per jenis aksi
    $eventMeta = [
        'created'          => ['Ditambahkan', 'fa-plus',               'bg-emerald-100 text-emerald-700', 'bg-emerald-500'],
        'updated'          => ['Diperbarui',  'fa-pen',                'bg-blue-100 text-blue-700',       'bg-blue-500'],
        'deleted'          => ['Dihapus',     'fa-trash',              'bg-rose-100 text-rose-700',       'bg-rose-500'],
        'login'            => ['Masuk Akun',  'fa-right-to-bracket',   'bg-indigo-100 text-indigo-700',   'bg-indigo-500'],
        'logout'           => ['Keluar Akun', 'fa-right-from-bracket', 'bg-slate-100 text-slate-700',     'bg-slate-400'],
        'register'         => ['Daftar Akun', 'fa-user-plus',          'bg-purple-100 text-purple-700',   'bg-purple-500'],
        'view'             => ['Lihat Produk','fa-eye',                'bg-teal-100 text-teal-700',       'bg-teal-500'],
        'search'           => ['Pencarian',   'fa-magnifying-glass',   'bg-sky-100 text-sky-700',         'bg-sky-500'],
        'add_to_cart'      => ['Keranjang',   'fa-cart-plus',          'bg-amber-100 text-amber-700',     'bg-amber-500'],
        'remove_from_cart' => ['Hapus Item',  'fa-cart-arrow-down',    'bg-red-100 text-red-700',         'bg-red-400'],
        'checkout_view'    => ['Buka Kasir',  'fa-cash-register',      'bg-violet-100 text-violet-700',   'bg-violet-500'],
        'dwell'            => ['Dwell Time',  'fa-stopwatch',          'bg-amber-100 text-amber-700',     'bg-amber-500'],
    ];
    $moduleIcons = [
        'produk'         => 'fa-box-open',
        'pencarian'      => 'fa-magnifying-glass',
        'keranjang'      => 'fa-cart-shopping',
        'checkout'       => 'fa-cash-register',
        'auth'           => 'fa-shield-halved',
        'pesanan'        => 'fa-receipt',
        'banner'         => 'fa-images',
        'diskon'         => 'fa-tags',
        'kategori'       => 'fa-layer-group',
        'pengguna'       => 'fa-user',
        'pengaturan'     => 'fa-gear',
        'ulasan'         => 'fa-star',
        'pengembalian'   => 'fa-rotate-left',
        'rpay'           => 'fa-wallet',
        'rpaywithdrawal' => 'fa-money-bill-transfer',
        'evaluasi_web'  => 'fa-stopwatch',
    ];
    $hasFilter = collect($filters)->filter(fn($v) => $v !== '')->isNotEmpty();
    $currentTab = $tab ?? 'admin';
@endphp

<div class="space-y-6" x-data="{ showDetail: false, detail: {}, showPrune: false }">

    {{-- ── Kartu Evaluasi Customer Journey & Login ── --}}
    @if(isset($analytics))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Card 1: Statistik Login --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        Aktivitas Login
                    </span>
                    <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100">
                        {{ $analytics['login']['today'] }} Hari Ini
                    </span>
                </div>
                <div class="mt-3">
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-gray-800">{{ number_format($analytics['login']['total']) }}</span>
                        <span class="text-xs font-semibold text-gray-400">total masuk</span>
                    </div>
                    <div class="flex items-center gap-3 mt-2 text-[11px] font-semibold text-gray-500 pt-2 border-t border-gray-50">
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-user-shield text-blue-500 text-[10px]"></i>
                            Admin: <strong class="text-gray-700">{{ $analytics['login']['admin'] }}</strong>
                        </span>
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-user text-emerald-500 text-[10px]"></i>
                            Customer: <strong class="text-gray-700">{{ $analytics['login']['customer'] }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Card 2: Proporsi Perangkat --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-mobile-screen"></i>
                        </span>
                        Rasio Perangkat
                    </span>
                    <span class="text-[10px] font-semibold text-gray-400">
                        {{ number_format($analytics['devices']['total']) }} sampel
                    </span>
                </div>
                <div class="mt-3">
                    @php
                        $mobPct = $analytics['devices']['mobile_pct'];
                        $dskPct = $analytics['devices']['desktop_pct'];
                    @endphp
                    <div class="flex items-baseline justify-between mb-1.5">
                        <span class="text-xs font-black text-gray-700 flex items-center gap-1">
                            <i class="fa-solid fa-mobile-screen text-emerald-500"></i> HP {{ $mobPct }}%
                        </span>
                        <span class="text-xs font-black text-gray-700 flex items-center gap-1">
                            PC {{ $dskPct }}% <i class="fa-solid fa-laptop text-blue-500"></i>
                        </span>
                    </div>
                    <div class="flex h-2 rounded-full overflow-hidden bg-gray-100 mb-2">
                        <div class="bg-emerald-500" style="width: {{ $mobPct }}%"></div>
                        <div class="bg-blue-500" style="width: {{ $dskPct }}%"></div>
                    </div>
                    <p class="text-[10px] text-gray-400 font-medium truncate">
                        @if($mobPct >= 50)
                            Mayoritas berbelanja lewat HP (Mobile).
                        @else
                            Pengunjung berimbang antara Desktop & Mobile.
                        @endif
                    </p>
                </div>
            </div>

            {{-- Card 3: Top Produk Dilihat --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                        Produk Sering Dilihat
                    </span>
                    <span class="text-[10px] font-semibold text-gray-400">7 hari</span>
                </div>
                <div class="mt-2 space-y-1">
                    @forelse($analytics['top_products'] as $tp)
                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="font-bold text-gray-700 truncate max-w-[160px]" title="{{ $tp['name'] }}">
                                {{ $tp['name'] }}
                            </span>
                            <span class="text-[9px] font-black px-1.5 py-0.2 rounded-full bg-teal-50 text-teal-700 border border-teal-100 shrink-0">
                                {{ $tp['views'] }}x
                            </span>
                        </div>
                    @empty
                        <p class="text-[11px] text-gray-400 italic py-1.5">Belum ada rekaman klik produk.</p>
                    @endforelse
                </div>
            </div>

            {{-- Card 4: Top Pencarian --}}
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        Pencarian Terpopuler
                    </span>
                    <span class="text-[10px] font-semibold text-gray-400">7 hari</span>
                </div>
                <div class="mt-2.5 flex flex-wrap gap-1.5">
                    @forelse($analytics['top_searches'] as $kw => $freq)
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700">
                            <span>"{{ $kw }}"</span>
                            <span class="text-[9px] font-black px-1.5 py-0.2 rounded-full bg-sky-100 text-sky-700">{{ $freq }}</span>
                        </span>
                    @empty
                        <p class="text-[11px] text-gray-400 italic py-1.5">Belum ada data pencarian.</p>
                    @endforelse
                </div>
            </div>
            {{-- Card 5 - Full Width: Dwell Time Seksi Website (Collapsible & Compact) --}}
            @php
                $topDwell = !empty($analytics['dwell_sections']) ? $analytics['dwell_sections'][0] : null;
            @endphp
            <div class="md:col-span-2 lg:col-span-4 bg-white rounded-2xl p-4 shadow-sm border border-amber-100/80 transition-all"
                 x-data="{ showDwell: localStorage.getItem('admin_dwell_open') === '1' }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm shadow-sm border border-amber-200/50 shrink-0">
                            <i class="fa-solid fa-stopwatch"></i>
                        </span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="text-xs font-black text-gray-800 uppercase tracking-wide">
                                    Evaluasi Atensi Seksi Website
                                </h4>
                                @if(!empty($analytics['dwell_total_secs']))
                                    <span class="text-[10px] font-bold text-amber-700 bg-amber-100/80 px-2 py-0.5 rounded-full border border-amber-200">
                                        ⏱️ {{ gmdate('H:i:s', $analytics['dwell_total_secs']) }} total
                                    </span>
                                @endif
                                @if($topDwell)
                                    <span class="hidden sm:inline-flex items-center gap-1 text-[10px] font-semibold text-gray-500 bg-amber-50/60 px-2 py-0.5 rounded-md border border-amber-100">
                                        <i class="fa-solid fa-fire text-amber-500 text-[9px]"></i> Teratas: <strong class="text-gray-800">{{ $topDwell['label'] }}</strong> ({{ $topDwell['pct'] }}%)
                                    </span>
                                @endif
                            </div>
                            <p class="text-[11px] text-gray-400 font-medium">Seksi yang paling sering & paling lama diperhatikan pengunjung toko</p>
                        </div>
                    </div>

                    {{-- Actions: Period Filter + Collapse Toggle --}}
                    <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                        {{-- Quick Period Filter Pills --}}
                        <div class="flex items-center gap-1 bg-amber-50/60 p-1 rounded-xl border border-amber-100">
                            @php
                                $currentDwellPeriod = $dwellPeriod ?? '30d';
                                $periodOptions = [
                                    'today' => 'Hari Ini',
                                    '7d'    => '7 Hari',
                                    '30d'   => '30 Hari',
                                    'all'   => 'Semua',
                                ];
                            @endphp
                            @foreach($periodOptions as $pKey => $pLabel)
                                <a href="{{ request()->fullUrlWithQuery(['dwell_period' => $pKey]) }}"
                                   class="px-2 py-0.5 rounded-lg text-[10px] font-bold transition-all {{ $currentDwellPeriod === $pKey ? 'bg-amber-500 text-white shadow-sm' : 'text-amber-900/70 hover:text-amber-900 hover:bg-amber-100/60' }}">
                                    {{ $pLabel }}
                                </a>
                            @endforeach
                        </div>

                        {{-- Toggle Button Accordion --}}
                        <button type="button"
                                @click="showDwell = !showDwell; localStorage.setItem('admin_dwell_open', showDwell ? '1' : '0')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer"
                                :class="showDwell ? 'bg-amber-500 text-white shadow-sm hover:bg-amber-600' : 'bg-amber-100/80 hover:bg-amber-200 text-amber-900 border border-amber-200/80'">
                            <i class="fa-solid fa-chart-simple text-[10px]"></i>
                            <span x-text="showDwell ? 'Tutup Rincian' : 'Rincian Atensi'"></span>
                            <i class="fa-solid text-[9px] transition-transform duration-200" :class="showDwell ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                    </div>
                </div>

                {{-- Collapsible Content: Hanya terbuka saat showDwell == true --}}
                <div x-show="showDwell" x-transition.opacity.duration.200ms x-cloak class="mt-4 pt-3 border-t border-amber-50">
                    @if(!empty($analytics['dwell_sections']))
                    {{-- Header Kolom Data (Lurus & Presisi) --}}
                    <div class="hidden sm:flex items-center justify-between px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-400 border-b border-gray-100 mb-1.5">
                        <div class="flex-1 min-w-0 pr-6">Seksi & Distribusi Atensi</div>
                        <div class="w-[330px] shrink-0 flex items-center gap-3">
                            <div class="w-[76px] shrink-0 text-center">Total Durasi</div>
                            <div class="w-[64px] shrink-0 text-right">Rata-Rata</div>
                            <div class="w-[40px] shrink-0 text-right">Tayang</div>
                            <div class="w-[114px] shrink-0 text-center">Pengunjung</div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        @foreach($analytics['dwell_sections'] as $dw)
                        @php
                            $dwTotMins = intdiv($dw['total_seconds'], 60);
                            $dwTotSecs = $dw['total_seconds'] % 60;
                            $totFmt = ($dwTotMins > 0 ? $dwTotMins . 'm ' : '') . $dwTotSecs . 'd';

                            $dwMins = intdiv($dw['avg_seconds'], 60);
                            $dwSecs = $dw['avg_seconds'] % 60;
                            $avgFmt = ($dwMins > 0 ? $dwMins . 'm ' : '') . $dwSecs . 'd';

                            $barColor = match(true) {
                                $dw['pct'] >= 30 => 'bg-gradient-to-r from-amber-500 to-orange-500',
                                $dw['pct'] >= 15 => 'bg-gradient-to-r from-amber-400 to-amber-500',
                                default          => 'bg-gradient-to-r from-amber-300 to-amber-400',
                            };
                        @endphp
                        <div class="p-2.5 rounded-xl bg-slate-50/70 hover:bg-amber-50/40 border border-gray-100 hover:border-amber-200 transition-all">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                {{-- Kolom Kiri: Seksi, Halaman, Persentase & Progress Bar --}}
                                <div class="flex-1 min-w-0 sm:pr-6">
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="text-xs font-bold text-gray-800 truncate" title="{{ $dw['label'] }}">
                                                {{ $dw['label'] }}
                                            </span>
                                            @if(!empty($dw['pages']))
                                                <span class="text-[9px] font-medium text-gray-400 bg-white px-1.5 py-0.5 rounded border border-gray-200 truncate max-w-[140px]">
                                                    {{ implode(', ', array_slice($dw['pages'], 0, 2)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-[11px] font-black text-amber-700 shrink-0 w-12 text-right tabular-nums">{{ $dw['pct'] }}%</span>
                                    </div>
                                    <div class="h-1.5 w-full rounded-full bg-gray-200/70 overflow-hidden shadow-inner">
                                        <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-500" style="width: {{ min(100, $dw['pct']) }}%"></div>
                                    </div>
                                </div>

                                {{-- Kolom Kanan: Metrik dengan Lebar Tetap (Lurus & Presisi) --}}
                                <div class="w-full sm:w-[330px] shrink-0 flex items-center justify-between sm:justify-start gap-3">
                                    {{-- 1. Total Durasi (Fixed 76px) --}}
                                    <div class="w-[76px] shrink-0 text-center">
                                        <span class="inline-block w-full text-amber-800 bg-amber-100/70 py-0.5 rounded-md border border-amber-200/60 font-bold text-[10.5px] tabular-nums" title="Total Durasi Diperhatikan">
                                            {{ $totFmt }}
                                        </span>
                                    </div>

                                    {{-- 2. Rata-rata (Fixed 64px) --}}
                                    <div class="w-[64px] shrink-0 text-right text-gray-500 font-bold text-[10.5px] tabular-nums" title="Rata-rata Durasi per Tayang">
                                        ⌀ {{ $avgFmt }}
                                    </div>

                                    {{-- 3. Tayang (Fixed 40px) --}}
                                    <div class="w-[40px] shrink-0 text-right text-gray-400 font-bold text-[10.5px] tabular-nums" title="Total Tayang">
                                        {{ $dw['views'] }}x
                                    </div>

                                    {{-- 4. Pengunjung (Fixed 114px) --}}
                                    <div class="w-[114px] shrink-0">
                                        @if(!empty($dw['viewers']))
                                            <div class="relative" x-data="{ openViewers: false }">
                                                <button type="button" @click="openViewers = !openViewers"
                                                        class="w-full inline-flex items-center justify-between px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/70 transition-colors cursor-pointer"
                                                        title="Klik untuk rincian pengunjung">
                                                    <span class="flex items-center gap-1 min-w-0 truncate">
                                                        <i class="fa-solid fa-users text-[9px] shrink-0"></i>
                                                        <span class="truncate">{{ $dw['unique_viewers_count'] }} pengunjung</span>
                                                    </span>
                                                    <i class="fa-solid fa-caret-down text-[8px] opacity-70 shrink-0 ml-1"></i>
                                                </button>

                                                {{-- Dropdown Floating Popover --}}
                                                <div x-show="openViewers" @click.outside="openViewers = false" x-cloak
                                                     class="absolute right-0 top-full mt-1.5 w-72 p-2.5 bg-white rounded-xl shadow-xl border border-gray-100 z-30 text-left">
                                                    <div class="flex items-center justify-between pb-1.5 mb-1.5 border-b border-gray-100">
                                                        <span class="text-[10px] font-black text-gray-500 uppercase tracking-wider">
                                                            <i class="fa-solid fa-user-clock mr-1 text-indigo-500"></i> Pengunjung Seksi
                                                        </span>
                                                        <span class="text-[9px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.2 rounded">
                                                            {{ $dw['unique_viewers_count'] }} total
                                                        </span>
                                                    </div>
                                                    <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                                                        @foreach($dw['viewers'] as $v)
                                                        @php
                                                            $vMins = intdiv($v['seconds'], 60);
                                                            $vSecs = $v['seconds'] % 60;
                                                            $vFmt = ($vMins > 0 ? $vMins . 'm ' : '') . $vSecs . 'd';
                                                        @endphp
                                                        <div class="flex items-center justify-between text-[10px] py-0.5">
                                                            <div class="flex items-center gap-1.5 min-w-0 pr-2">
                                                                <i class="fa-solid {{ $v['icon'] ?? 'fa-user' }} text-[9px] text-gray-400"></i>
                                                                <div class="truncate">
                                                                    <p class="font-semibold text-gray-800 truncate">{{ $v['name'] }}</p>
                                                                    <p class="text-[8px] text-gray-400 truncate">{{ $v['sub'] }} · {{ $v['count'] }}x</p>
                                                                </div>
                                                            </div>
                                                            <span class="font-bold text-amber-700 shrink-0 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-100 tabular-nums">
                                                                {{ $vFmt }}
                                                            </span>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    @if($dw['unique_viewers_count'] > count($dw['viewers']))
                                                        <p class="text-[9px] text-gray-400 text-center pt-1.5 mt-1 border-t border-gray-100">
                                                            +{{ $dw['unique_viewers_count'] - count($dw['viewers']) }} pengunjung lainnya
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="w-full text-center">
                                                <span class="inline-block w-full text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100 text-[10px] font-bold">
                                                    <i class="fa-solid fa-users mr-1 text-[9px]"></i>{{ $dw['unique_viewers_count'] }} pengunjung
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="py-4 px-4 rounded-xl bg-amber-50/40 border border-dashed border-amber-200 text-center">
                        <p class="text-xs font-bold text-amber-900">Belum ada data atensi seksi pada periode ini</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Data direkam otomatis saat pengunjung melihat seksi minimal 3 detik.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ── Statistik Umum ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400">Total Catatan</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ number_format($stats['total']) }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400">Hari Ini</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ number_format($stats['today']) }}</h3>
                <p class="text-[10px] text-gray-400">{{ number_format($stats['week']) }} dalam 7 hari</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-gray-400 mb-2.5">Sebaran Aksi</p>
            @php $eventTotal = max(1, $stats['created'] + $stats['updated'] + $stats['deleted']); @endphp
            <div class="flex h-2 rounded-full overflow-hidden bg-gray-100 mb-2">
                <div class="bg-emerald-500" style="width: {{ $stats['created'] / $eventTotal * 100 }}%"></div>
                <div class="bg-blue-500" style="width: {{ $stats['updated'] / $eventTotal * 100 }}%"></div>
                <div class="bg-rose-500" style="width: {{ $stats['deleted'] / $eventTotal * 100 }}%"></div>
            </div>
            <div class="flex flex-wrap gap-x-3 gap-y-1 text-[10px] font-bold">
                <span class="text-emerald-600">● {{ $stats['created'] }} tambah</span>
                <span class="text-blue-600">● {{ $stats['updated'] }} ubah</span>
                <span class="text-rose-600">● {{ $stats['deleted'] }} hapus</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col justify-between">
            <p class="text-xs font-semibold text-gray-400">Perawatan Log</p>
            <div class="flex flex-wrap gap-2 mt-2.5">
                <a href="{{ route('admin.activity-logs.export', request()->except('page')) }}"
                   class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white transition">
                    <i class="fa-solid fa-file-csv"></i>Export
                </a>
                <button @click="showPrune = true"
                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-xl border border-red-200 text-red-500 hover:bg-red-50 transition">
                    <i class="fa-solid fa-broom"></i>Bersihkan
                </button>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="flash-alert p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm shadow-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Tab Navigasi Pill ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-1.5 flex gap-1">
        @php
            $tabConfig = [
                'admin' => [
                    'label'  => 'Aktivitas Admin',
                    'icon'   => 'fa-user-shield',
                    'count'  => $tabCounts['admin'],
                    'color'  => $currentTab === 'admin' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100',
                    'badge'  => 'bg-blue-100 text-blue-700',
                ],
                'user' => [
                    'label'  => 'User & Tamu',
                    'icon'   => 'fa-users',
                    'count'  => $tabCounts['user'],
                    'color'  => $currentTab === 'user' ? 'bg-emerald-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100',
                    'badge'  => 'bg-emerald-100 text-emerald-700',
                ],
                'all' => [
                    'label'  => 'Semua / Sistem',
                    'icon'   => 'fa-server',
                    'count'  => $tabCounts['all'],
                    'color'  => $currentTab === 'all' ? 'bg-slate-700 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100',
                    'badge'  => 'bg-slate-100 text-slate-700',
                ],
            ];
        @endphp
        @foreach ($tabConfig as $tabKey => $tc)
            <a href="{{ route('admin.activity-logs', array_merge(request()->except(['tab','page']), ['tab' => $tabKey])) }}"
               class="flex-1 flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-xs font-bold transition-all duration-150 {{ $tc['color'] }}">
                <i class="fa-solid {{ $tc['icon'] }}"></i>
                <span>{{ $tc['label'] }}</span>
                <span class="ml-auto text-[10px] font-black px-2 py-0.5 rounded-full {{ $currentTab === $tabKey ? 'bg-white/25 text-white' : $tc['badge'] }}">
                    {{ number_format($tc['count']) }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- ── Filter ── --}}
    <form action="{{ route('admin.activity-logs') }}" method="GET"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        {{-- Pertahankan tab aktif saat filter diterapkan --}}
        <input type="hidden" name="tab" value="{{ $currentTab }}">

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-3 items-end">

            <div class="xl:col-span-2">
                <label class="block text-[10px] font-black text-gray-500 uppercase mb-1.5">Cari</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                           placeholder="Deskripsi, IP, atau isi perubahan..."
                           class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-500 uppercase mb-1.5">Modul</label>
                <select name="log_name" class="w-full border border-gray-200 rounded-xl py-2 px-3 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    <option value="">Semua Modul</option>
                    @foreach($logNames as $name)
                        <option value="{{ $name }}" @selected($filters['log_name'] === $name)>{{ ucfirst($name) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-500 uppercase mb-1.5">Aksi</label>
                <select name="event" class="w-full border border-gray-200 rounded-xl py-2 px-3 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    <option value="">Semua Aksi</option>
                    @foreach($eventMeta as $key => [$evLabel])
                        <option value="{{ $key }}" @selected($filters['event'] === $key)>{{ $evLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-500 uppercase mb-1.5">
                    {{ $currentTab === 'user' ? 'Pelaku (Customer)' : 'Pelaku' }}
                </label>
                <select name="causer" class="w-full border border-gray-200 rounded-xl py-2 px-3 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    <option value="">Semua Pelaku</option>
                    @foreach($causers as $user)
                        <option value="{{ $user->id }}" @selected((string) $filters['causer'] === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                    @if($currentTab === 'user')
                        <option value="__guest__" @selected($filters['causer'] === '__guest__')>Tamu (Guest / Tanpa Login)</option>
                    @endif
                </select>
            </div>

            <div class="relative" x-data="activityLogDateRangePicker('{{ $filters['from'] }}', '{{ $filters['to'] }}')">
                <label class="block text-[10px] font-black text-gray-500 uppercase mb-1.5">Rentang Tanggal</label>

                {{-- Input tersembunyi yang dikirimkan form ke controller --}}
                <input type="hidden" name="from" :value="from" x-ref="fromInput">
                <input type="hidden" name="to" :value="to" x-ref="toInput">

                {{-- Trigger Input Kalender --}}
                <button type="button"
                        @click="togglePicker()"
                        class="w-full border border-gray-200 rounded-xl py-2 px-3 text-xs bg-white flex items-center justify-between text-left focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition shadow-sm hover:border-gray-300">
                    <span class="truncate pr-1"
                          :class="(from || to) ? 'text-gray-900 font-bold' : 'text-gray-400 font-medium'"
                          x-text="displayLabel">
                    </span>
                    <div class="flex items-center gap-1.5 text-gray-400 shrink-0">
                        <template x-if="from || to">
                            <span @click.stop="clearDate()"
                                  title="Hapus filter tanggal"
                                  class="hover:text-red-500 p-0.5 rounded cursor-pointer transition">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </span>
                        </template>
                        <i class="fa-regular fa-calendar text-xs text-orange-500"></i>
                    </div>
                </button>

                {{-- Popover Kalender Rentang Tanggal (Gaya Shopee Seller Center) --}}
                <div x-show="open"
                     @click.outside="open = false"
                     x-cloak
                     class="absolute right-0 top-full mt-2 w-[285px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-4 z-50 select-none animate-in fade-in duration-150">

                    {{-- Header Navigasi Kalender: << < September2026 > >> --}}
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div class="flex items-center gap-0.5">
                            <button type="button" @click="changeYear(-1)" title="Tahun Sebelumnya"
                                    class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                                <i class="fa-solid fa-angles-left text-xs"></i>
                            </button>
                            <button type="button" @click="changeMonth(-1)" title="Bulan Sebelumnya"
                                    class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                                <i class="fa-solid fa-angle-left text-xs"></i>
                            </button>
                        </div>

                        <span class="font-bold text-gray-800 text-sm tracking-tight" x-text="monthYearTitle"></span>

                        <div class="flex items-center gap-0.5">
                            <button type="button" @click="changeMonth(1)" :disabled="isCurrentMonthAndYear"
                                    :class="isCurrentMonthAndYear ? 'text-gray-200 cursor-not-allowed' : 'text-gray-400 hover:text-gray-700 hover:bg-gray-100'"
                                    title="Bulan Berikutnya"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg transition">
                                <i class="fa-solid fa-angle-right text-xs"></i>
                            </button>
                            <button type="button" @click="changeYear(1)" :disabled="isCurrentYear"
                                    :class="isCurrentYear ? 'text-gray-200 cursor-not-allowed' : 'text-gray-400 hover:text-gray-700 hover:bg-gray-100'"
                                    title="Tahun Berikutnya"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg transition">
                                <i class="fa-solid fa-angles-right text-xs"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Baris Singkatan Hari: M S S R K J S --}}
                    <div class="grid grid-cols-7 mb-2 text-center text-xs font-semibold text-gray-500">
                        <div>M</div>
                        <div>S</div>
                        <div>S</div>
                        <div>R</div>
                        <div>K</div>
                        <div>J</div>
                        <div>S</div>
                    </div>

                    {{-- Grid Tanggal --}}
                    <div class="grid grid-cols-7 gap-y-1.5 text-xs" @mouseleave="hoverDate = null">
                        {{-- Slot Kosong Hari Sebelum Tanggal 1 --}}
                        <template x-for="blank in blanks" :key="'blank-' + blank">
                            <div class="h-8"></div>
                        </template>

                        {{-- Hari dalam Bulan --}}
                        <template x-for="day in daysInMonth" :key="'day-' + day">
                            <button type="button"
                                    @click="selectDate(day)"
                                    @mouseenter="onHoverDay(day)"
                                    :disabled="getDayState(day) === 'disabled'"
                                    class="h-8 flex items-center justify-center text-xs transition-colors duration-75 relative select-none"
                                    :class="{
                                        'bg-[#EE4D2D] text-white font-bold rounded-lg shadow-sm z-10': getDayState(day) === 'single',
                                        'bg-[#EE4D2D] text-white font-bold rounded-l-lg shadow-sm z-10': getDayState(day) === 'range-start',
                                        'bg-[#EE4D2D] text-white font-bold rounded-r-lg shadow-sm z-10': getDayState(day) === 'range-end',
                                        'bg-[#FFF0ED] text-gray-800 font-semibold rounded-none': getDayState(day) === 'in-range',
                                        'text-gray-300 cursor-not-allowed': getDayState(day) === 'disabled',
                                        'text-gray-700 hover:bg-orange-50 hover:text-[#EE4D2D] rounded-lg cursor-pointer font-medium': getDayState(day) === 'available'
                                    }">
                                <span x-text="day"></span>
                            </button>
                        </template>
                    </div>

                    {{-- Footer Popover: Hapus / Tutup / Terapkan --}}
                    <div class="flex items-center justify-between pt-3 mt-3 border-t border-gray-100 text-[11px]">
                        <button type="button" @click="clearDate(); open = false"
                                class="text-gray-400 hover:text-red-500 font-semibold transition">
                            Hapus
                        </button>
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="open = false"
                                    class="px-2.5 py-1 text-gray-500 hover:bg-gray-100 rounded-lg font-bold transition">
                                Tutup
                            </button>
                            <button type="button" @click="applySelection()"
                                    class="px-3 py-1 bg-[#EE4D2D] hover:bg-[#D73211] text-white rounded-lg font-bold shadow-sm transition">
                                Terapkan
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 mt-4 pt-3.5 border-t border-gray-100">
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow-sm hover:shadow transition flex items-center gap-1.5">
                    <i class="fa-solid fa-filter text-xs"></i>Terapkan Filter
                </button>
                @if($hasFilter)
                    <a href="{{ route('admin.activity-logs', ['tab' => $currentTab]) }}"
                       class="text-xs font-bold px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-rotate-left text-xs"></i>Reset Filter
                    </a>
                @endif
            </div>

            {{-- Quick Date Range Buttons --}}
            <div class="flex items-center gap-1.5 bg-gray-50 px-2 py-1 rounded-xl border border-gray-100">
                <span class="text-[10px] font-black text-gray-400 uppercase mr-1">Filter Cepat:</span>
                <button type="button" @click="
                    const fmt = d => {
                        const y = d.getFullYear();
                        const m = String(d.getMonth() + 1).padStart(2, '0');
                        const day = String(d.getDate()).padStart(2, '0');
                        return `${y}-${m}-${day}`;
                    };
                    const t = new Date();
                    const f = $el.closest('form');
                    f.querySelector('input[name=from]').value = fmt(t);
                    f.querySelector('input[name=to]').value = fmt(t);
                    f.submit();
                " class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white hover:bg-orange-500 hover:text-white border border-gray-200 text-gray-700 transition">Hari Ini</button>

                <button type="button" @click="
                    const fmt = d => {
                        const y = d.getFullYear();
                        const m = String(d.getMonth() + 1).padStart(2, '0');
                        const day = String(d.getDate()).padStart(2, '0');
                        return `${y}-${m}-${day}`;
                    };
                    const t = new Date();
                    const d = new Date(); d.setDate(d.getDate() - 7);
                    const f = $el.closest('form');
                    f.querySelector('input[name=from]').value = fmt(d);
                    f.querySelector('input[name=to]').value = fmt(t);
                    f.submit();
                " class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white hover:bg-orange-500 hover:text-white border border-gray-200 text-gray-700 transition">7 Hari</button>

                <button type="button" @click="
                    const fmt = d => {
                        const y = d.getFullYear();
                        const m = String(d.getMonth() + 1).padStart(2, '0');
                        const day = String(d.getDate()).padStart(2, '0');
                        return `${y}-${m}-${day}`;
                    };
                    const t = new Date();
                    const d = new Date(); d.setDate(d.getDate() - 30);
                    const f = $el.closest('form');
                    f.querySelector('input[name=from]').value = fmt(d);
                    f.querySelector('input[name=to]').value = fmt(t);
                    f.submit();
                " class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-white hover:bg-orange-500 hover:text-white border border-gray-200 text-gray-700 transition">30 Hari</button>
            </div>

            <span class="text-xs text-gray-500 font-bold ml-auto bg-slate-50 px-3 py-1.5 rounded-xl border border-gray-100">
                <span class="text-gray-900 font-black">{{ number_format($logs->total()) }}</span> catatan ditemukan
            </span>
        </div>
    </form>

    {{-- ── Linimasa Log ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        @if($logs->isEmpty())
            <div class="p-16 text-center">
                <i class="fa-regular fa-clock text-4xl text-gray-300 mb-4 block"></i>
                <h3 class="text-sm font-bold text-gray-500">
                    {{ $hasFilter ? 'Tidak ada log yang cocok dengan filter' : 'Belum ada catatan aktivitas' }}
                </h3>
                <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto leading-relaxed">
                    {{ $hasFilter
                        ? 'Coba longgarkan filter atau perluas rentang tanggalnya.'
                        : 'Log akan terisi otomatis begitu ada perubahan data — misalnya menambah produk, mengubah status pesanan, atau menyimpan pengaturan.' }}
                </p>
            </div>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($logs as $log)
                    @php
                        [$evLabel, $evIcon, $evBadge, $evDot] = $eventMeta[$log->event] ?? ['Aktivitas', 'fa-circle-info', 'bg-gray-100 text-gray-700', 'bg-gray-400'];
                        $moduleIcon = $moduleIcons[$log->log_name] ?? 'fa-circle-dot';

                        $props      = is_array($log->properties) ? $log->properties : (is_object($log->properties) && method_exists($log->properties, 'toArray') ? $log->properties->toArray() : []);
                        $attributes = $props['attributes'] ?? [];
                        $old        = $props['old'] ?? [];
                        $changeKeys = array_keys($attributes);

                        // Resolve actor & device info
                        $actorInfo = DeviceDetector::actorInfo($log);
                        $device    = DeviceDetector::fromActivity($log);

                        $payload = [
                            'description'   => $log->description,
                            'module'        => ucfirst($log->log_name),
                            'event'         => $evLabel,
                            'causer'        => $actorInfo['name'] ?? 'Sistem',
                            'causerEmail'   => $actorInfo['email'] ?? '—',
                            'causerRole'    => $actorInfo['role_label'] ?? 'Sistem',
                            'isGuest'       => $actorInfo['is_guest'] ?? false,
                            'subject'       => class_basename($log->subject_type ?? '') . ($log->subject_id ? ' #' . $log->subject_id : ''),
                            'time'          => $log->created_at?->translatedFormat('l, d F Y · H:i:s'),
                            'ago'           => $log->created_at?->diffForHumans(),
                            'attributes'    => $attributes,
                            'old'           => $old,
                            'deviceType'    => $device['device_type'] ?? 'Desktop',
                            'deviceLabel'   => $device['formatted'] ?? ($device['device_type'] ?? 'Desktop'),
                            'devicePlatform'=> $device['platform'] ?? '-',
                            'deviceBrowser' => $device['browser'] ?? '-',
                            'ip'            => $props['ip'] ?? null,
                            'userAgent'     => $props['user_agent'] ?? null,
                            'props'         => $props,
                        ];
                    @endphp

                    <div class="px-5 py-4 hover:bg-slate-50/60 transition flex items-start gap-4">

                        {{-- Titik aksi --}}
                        <div class="shrink-0 pt-0.5">
                            <span class="w-9 h-9 rounded-xl {{ $evBadge }} flex items-center justify-center">
                                <i class="fa-solid {{ $evIcon }} text-xs"></i>
                            </span>
                        </div>

                        {{-- Isi --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                                {{-- Module badge --}}
                                <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                    <i class="fa-solid {{ $moduleIcon }} text-[9px]"></i>{{ $log->log_name }}
                                </span>
                                {{-- Action badge --}}
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full {{ $evBadge }}">{{ $evLabel }}</span>
                                {{-- Subject --}}
                                @if($log->subject_id)
                                    <code class="text-[10px] text-gray-400">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</code>
                                @endif
                            </div>

                            <p class="text-xs font-bold text-gray-800 leading-relaxed">{{ ucfirst($log->description) }}</p>

                            {{-- Inline Preview: Produk yang dilihat --}}
                            @if($log->log_name === 'produk' && !empty($props['product_name']))
                                <div class="inline-flex items-center gap-2 mt-1.5 px-2.5 py-1 rounded-lg bg-teal-50 border border-teal-100 text-xs font-semibold text-teal-800">
                                    <span><i class="fa-solid fa-box-open mr-1 text-teal-600"></i>{{ $props['product_name'] }}</span>
                                    @if(!empty($props['category']) && $props['category'] !== '-')
                                        <span class="opacity-60">· {{ $props['category'] }}</span>
                                    @endif
                                    @if(!empty($props['price']))
                                        <span class="font-bold text-teal-900">· Rp {{ number_format($props['price'], 0, ',', '.') }}</span>
                                    @endif
                                    @if(isset($props['stock']))
                                        <span class="text-[10px] text-gray-500">· Stok: {{ $props['stock'] }}</span>
                                    @endif
                                </div>
                            {{-- Inline Preview: Pencarian --}}
                            @elseif($log->log_name === 'pencarian' && !empty($props['keyword']))
                                <div class="inline-flex items-center gap-1.5 mt-1.5 px-2.5 py-1 rounded-lg bg-sky-50 border border-sky-100 text-xs font-semibold text-sky-800">
                                    <i class="fa-solid fa-magnifying-glass text-sky-600"></i>
                                    <span>Kata Kunci: <strong>"{{ $props['keyword'] }}"</strong> ({{ $props['results_count'] ?? 0 }} hasil)</span>
                                </div>
                            {{-- Inline Preview: Evaluasi Web (Dwell) --}}
                            @elseif($log->log_name === 'evaluasi_web')
                                @php
                                    $dwellLabel = $props['label'] ?? $props['section_label'] ?? $props['section'] ?? $props['section_id'] ?? 'Seksi';
                                    $dwellSecs = (int)($props['seconds'] ?? $props['duration_seconds'] ?? 0);
                                    $dwellPage = $props['page'] ?? $props['page_url'] ?? $props['page_name'] ?? '';
                                    if ($dwellSecs < 60) {
                                        $dwellFmt = $dwellSecs . ' detik';
                                    } else {
                                        $dwM = intdiv($dwellSecs, 60);
                                        $dwS = $dwellSecs % 60;
                                        $dwellFmt = ($dwM > 0 ? $dwM . 'm ' : '') . ($dwS > 0 ? $dwS . 'd' : '');
                                    }
                                @endphp
                                <div class="inline-flex items-center gap-2 mt-1.5 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-900">
                                    <span><i class="fa-solid fa-stopwatch mr-1 text-amber-600"></i>{{ $dwellLabel }}</span>
                                    @if(!empty($dwellPage))
                                        <span class="opacity-60 text-[11px]">· {{ $dwellPage }}</span>
                                    @endif
                                    <span class="font-bold text-amber-800 bg-amber-100/80 px-2 py-0.2 rounded-md">· {{ $dwellFmt }}</span>
                                </div>
                            @endif

                            {{-- Changed fields --}}
                            @if(!empty($changeKeys))
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach(array_slice($changeKeys, 0, 5) as $key)
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-blue-50 text-blue-600 border border-blue-100">{{ $key }}</span>
                                    @endforeach
                                    @if(count($changeKeys) > 5)
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-gray-100 text-gray-500">+{{ count($changeKeys) - 5 }} lainnya</span>
                                    @endif
                                </div>
                            @endif

                            {{-- Actor + Device + Time row --}}
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2.5">
                                {{-- Actor badge --}}
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $actorInfo['badge_class'] ?? 'bg-gray-100 text-gray-700' }}">
                                    <i class="fa-solid {{ $actorInfo['icon'] ?? 'fa-user' }} text-[9px]"></i>
                                    {{ $actorInfo['name'] ?? 'Sistem' }}
                                    @if(!empty($actorInfo['is_guest']))
                                        <span class="opacity-70">· Tamu</span>
                                    @endif
                                </span>
                                {{-- Device badge: brand (mobile) atau platform+browser (desktop) --}}
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $device['badge_class'] ?? 'bg-slate-100 text-slate-700 border-slate-200' }} border"
                                      title="{{ $device['formatted'] ?? '' }}">
                                    <i class="fa-solid {{ $device['icon'] ?? 'fa-desktop' }} text-[9px]"></i>
                                    @if(!empty($device['brand']))
                                        {{-- Mobile/Tablet: tampilkan brand --}}
                                        {{ $device['brand'] }}
                                        <span class="opacity-60 font-normal">· {{ $device['platform'] ?? '' }}</span>
                                    @else
                                        {{-- Desktop: platform + browser digabung --}}
                                        {{ $device['platform'] ?? $device['device_type'] ?? 'Desktop' }}
                                        @if(!empty($device['browser']) && $device['browser'] !== 'Browser')
                                            <span class="opacity-60 font-normal">· {{ $device['browser'] }}</span>
                                        @endif
                                    @endif
                                </span>
                                {{-- IP Address --}}
                                @if(!empty($props['ip']))
                                    <span class="inline-flex items-center gap-1 text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 border border-slate-200">
                                        <i class="fa-solid fa-network-wired text-[8px]"></i>
                                        {{ $props['ip'] }}
                                    </span>
                                @endif
                                {{-- Time --}}
                                <span class="text-[10px] text-gray-400 font-semibold ml-auto" title="{{ $log->created_at }}">
                                    <i class="fa-solid fa-clock mr-1"></i>{{ $log->created_at?->translatedFormat('d M Y H:i') }} · {{ $log->created_at?->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        {{-- Tombol detail --}}
                        <button type="button" @click="detail = {{ Js::from($payload) }}; showDetail = true"
                                class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════ --}}
    {{-- MODAL: Detail Perubahan --}}
    {{-- ════════════════════════════════ --}}
    <div x-show="showDetail" x-transition style="display:none;"
         class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl flex flex-col" style="max-height:90vh;"
             @click.away="showDetail = false">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center shrink-0">
                <h2 class="font-black text-gray-800 text-sm uppercase tracking-wide">Detail Aktivitas</h2>
                <button @click="showDetail = false" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <div class="px-6 py-5 overflow-y-auto space-y-5" style="max-height:calc(90vh - 70px);">

                {{-- Ringkasan --}}
                <div>
                    <p class="text-sm font-black text-gray-800 leading-relaxed" x-text="detail.description"></p>
                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <p class="text-[10px] text-gray-400 font-black uppercase">Modul</p>
                            <p class="text-xs font-bold text-gray-800 mt-0.5" x-text="detail.module"></p>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <p class="text-[10px] text-gray-400 font-black uppercase">Aksi</p>
                            <p class="text-xs font-bold text-gray-800 mt-0.5" x-text="detail.event"></p>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <p class="text-[10px] text-gray-400 font-black uppercase">Pelaku</p>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <p class="text-xs font-bold text-gray-800" x-text="detail.causer"></p>
                                <span x-show="detail.isGuest" class="text-[9px] font-black px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 border border-amber-200">Tamu</span>
                            </div>
                            <p class="text-[10px] text-gray-400" x-text="detail.causerEmail"></p>
                            <p class="text-[10px] font-semibold mt-0.5" style="color:#6366f1" x-text="detail.causerRole"></p>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <p class="text-[10px] text-gray-400 font-black uppercase">Data Terkait</p>
                            <p class="text-xs font-bold text-gray-800 mt-0.5" x-text="detail.subject"></p>
                        </div>
                    </div>

                    {{-- Device Info Box --}}
                    <div class="mt-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <p class="text-[10px] text-gray-400 font-black uppercase mb-2">Informasi Perangkat</p>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
                            <div>
                                <p class="text-[10px] text-gray-400 font-semibold">Jenis Perangkat</p>
                                <p class="font-bold text-gray-700" x-text="detail.deviceType ?? '—'"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-semibold">Platform / OS</p>
                                <p class="font-bold text-gray-700" x-text="detail.devicePlatform ?? '—'"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-semibold">Browser</p>
                                <p class="font-bold text-gray-700" x-text="detail.deviceBrowser ?? '—'"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-semibold">Alamat IP</p>
                                <p class="font-bold text-gray-700" x-text="detail.ip ?? '—'"></p>
                            </div>
                        </div>
                        <template x-if="detail.userAgent">
                            <div class="mt-2 pt-2 border-t border-slate-200">
                                <p class="text-[10px] text-gray-400 font-semibold mb-0.5">User Agent</p>
                                <p class="text-[10px] text-gray-500 break-all leading-relaxed" x-text="detail.userAgent"></p>
                            </div>
                        </template>
                    </div>

                    {{-- Detail Khusus Produk yang Dilihat --}}
                    <template x-if="detail.props && detail.props.product_name">
                        <div class="mt-3 p-3.5 rounded-xl bg-teal-50/70 border border-teal-200/70">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-[10px] text-teal-700 font-black uppercase flex items-center gap-1.5">
                                    <i class="fa-solid fa-box-open"></i> Detail Produk yang Dilihat
                                </p>
                                <span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-teal-100 text-teal-800">
                                    ID: <span x-text="detail.props.product_id"></span>
                                </span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-semibold">Nama Produk</p>
                                    <p class="font-bold text-gray-800 truncate" x-text="detail.props.product_name"></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-semibold">Kategori</p>
                                    <p class="font-bold text-gray-800" x-text="detail.props.category ?? '-'"></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-semibold">Harga Saat Dilihat</p>
                                    <p class="font-bold text-teal-700" x-text="'Rp ' + Number(detail.props.price || 0).toLocaleString('id-ID')"></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-semibold">Sisa Stok</p>
                                    <p class="font-bold text-gray-800" x-text="(detail.props.stock || 0) + ' unit'"></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Detail Khusus Pencarian --}}
                    <template x-if="detail.props && detail.props.keyword">
                        <div class="mt-3 p-3.5 rounded-xl bg-sky-50/70 border border-sky-200/70">
                            <p class="text-[10px] text-sky-700 font-black uppercase flex items-center gap-1.5 mb-2">
                                <i class="fa-solid fa-magnifying-glass"></i> Pencarian Pengunjung
                            </p>
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-semibold">Kata Kunci</p>
                                    <p class="font-black text-gray-800 text-sm" x-text="'&ldquo;' + detail.props.keyword + '&rdquo;'"></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-semibold">Jumlah Hasil Ditemukan</p>
                                    <p class="font-bold text-sky-700" x-text="(detail.props.results_count || 0) + ' produk cocok'"></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Detail Khusus Keranjang Belanja --}}
                    <template x-if="detail.props && detail.props.quantity && !detail.props.product_slug">
                        <div class="mt-3 p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/70">
                            <p class="text-[10px] text-amber-800 font-black uppercase flex items-center gap-1.5 mb-2">
                                <i class="fa-solid fa-cart-shopping"></i> Interaksi Keranjang Belanja
                            </p>
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-semibold">Produk</p>
                                    <p class="font-bold text-gray-800 truncate" x-text="detail.props.product_name || '-'"></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-semibold">Varian</p>
                                    <p class="font-bold text-gray-800" x-text="detail.props.variant_name || 'Standar'"></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-semibold">Jumlah (Qty)</p>
                                    <p class="font-bold text-amber-700" x-text="detail.props.quantity + ' item'"></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Detail Khusus Dwell Time (Evaluasi Web) --}}
                    <template x-if="detail.props && (detail.props.section || detail.props.section_id)">
                        <div class="mt-3 p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/70">
                            <p class="text-[10px] text-amber-800 font-black uppercase flex items-center gap-1.5 mb-2">
                                <i class="fa-solid fa-stopwatch"></i> Evaluasi Atensi Seksi Website
                            </p>
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-semibold">Seksi</p>
                                    <p class="font-bold text-gray-800 truncate" x-text="detail.props.label || detail.props.section_label || detail.props.section || detail.props.section_id"></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-semibold">Halaman</p>
                                    <p class="font-bold text-gray-800" x-text="detail.props.page || detail.props.page_url || detail.props.page_name || '—'"></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 font-semibold">Durasi Atensi</p>
                                    <p class="font-bold text-amber-700"
                                        x-text="detail.props.duration_formatted || (Math.floor((detail.props.seconds || detail.props.duration_seconds || 0)/60) + 'm ' + ((detail.props.seconds || detail.props.duration_seconds || 0) % 60) + 'd')"></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <p class="text-[11px] text-gray-400 font-semibold mt-3">
                        <i class="fa-solid fa-clock mr-1"></i>
                        <span x-text="detail.time"></span> · <span x-text="detail.ago"></span>
                    </p>
                </div>

                {{-- Tabel perubahan nilai --}}
                <div>
                    <h3 class="text-xs font-black text-gray-800 uppercase tracking-wide mb-3">Perubahan Nilai</h3>

                    <template x-if="!detail.attributes || Object.keys(detail.attributes).length === 0">
                        <p class="text-xs text-gray-400 text-center py-6 bg-gray-50 rounded-xl border border-gray-100">
                            Tidak ada detail nilai yang tercatat untuk aktivitas ini.
                        </p>
                    </template>

                    <template x-if="detail.attributes && Object.keys(detail.attributes).length > 0">
                        <div class="border border-gray-100 rounded-2xl overflow-hidden">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-4 py-2.5 font-black text-gray-400 uppercase text-[10px]">Kolom</th>
                                        <th class="px-4 py-2.5 font-black text-gray-400 uppercase text-[10px]">Sebelum</th>
                                        <th class="px-4 py-2.5 font-black text-gray-400 uppercase text-[10px]">Sesudah</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <template x-for="(value, key) in detail.attributes" :key="key">
                                        <tr>
                                            <td class="px-4 py-2.5 font-bold text-gray-700 align-top" x-text="key"></td>
                                            <td class="px-4 py-2.5 align-top">
                                                <span class="inline-block px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-100 break-all"
                                                      x-text="(detail.old && detail.old[key] !== undefined && detail.old[key] !== null && detail.old[key] !== '') ? String(detail.old[key]) : '—'"></span>
                                            </td>
                                            <td class="px-4 py-2.5 align-top">
                                                <span class="inline-block px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100 break-all"
                                                      x-text="(value !== null && value !== '') ? String(value) : '—'"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════ --}}
    {{-- MODAL: Bersihkan Log Lama --}}
    {{-- ════════════════════════════════ --}}
    <div x-show="showPrune" x-transition style="display:none;"
         class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md" @click.away="showPrune = false">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h2 class="font-black text-gray-800 text-sm uppercase tracking-wide">Bersihkan Log Lama</h2>
                <button @click="showPrune = false" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <form action="{{ route('admin.activity-logs.prune') }}" method="POST"
                  onsubmit="return confirm('Hapus permanen log lama? Tindakan ini tidak bisa dibatalkan.')">
                @csrf @method('DELETE')
                <div class="px-6 py-5 space-y-4">
                    <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-[11px] flex items-start gap-2.5">
                        <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                        <span class="leading-relaxed">Log yang dihapus tidak bisa dikembalikan. Sebaiknya <strong>export dulu</strong> sebelum membersihkan.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Hapus log yang lebih tua dari</label>
                        <select name="days" class="w-full border border-gray-200 rounded-xl py-2.5 px-3 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                            <option value="30">30 hari</option>
                            <option value="90" selected>90 hari</option>
                            <option value="180">180 hari</option>
                            <option value="365">1 tahun</option>
                        </select>
                        <p class="text-[10px] text-gray-400 mt-1.5">Log yang lebih baru dari 7 hari selalu dipertahankan.</p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 rounded-b-3xl">
                    <button type="button" @click="showPrune = false"
                        class="text-xs font-bold px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">Batal</button>
                    <button type="submit"
                        class="text-xs font-bold px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white shadow transition">
                        <i class="fa-solid fa-broom mr-1.5"></i>Bersihkan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

{{-- Skrip Komponen Alpine.js untuk Kalender Rentang Tanggal --}}
<script>
function activityLogDateRangePicker(initialFrom, initialTo) {
    const today = new Date();
    const y = today.getFullYear();
    const m = String(today.getMonth() + 1).padStart(2, '0');
    const d = String(today.getDate()).padStart(2, '0');
    const todayStr = `${y}-${m}-${d}`;

    let initYear = today.getFullYear();
    let initMonth = today.getMonth();
    if (initialFrom) {
        const parts = initialFrom.split('-');
        if (parts.length === 3) {
            initYear = parseInt(parts[0], 10);
            initMonth = parseInt(parts[1], 10) - 1;
        }
    }

    return {
        open: false,
        from: initialFrom || '',
        to: initialTo || '',
        hoverDate: null,
        currentYear: initYear,
        currentMonth: initMonth,
        todayStr: todayStr,

        monthNames: [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ],
        shortMonthNames: [
            'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
        ],

        get monthYearTitle() {
            return this.monthNames[this.currentMonth] + this.currentYear;
        },

        get isCurrentMonthAndYear() {
            return this.currentYear === today.getFullYear() && this.currentMonth === today.getMonth();
        },

        get isCurrentYear() {
            return this.currentYear >= today.getFullYear();
        },

        get blanks() {
            const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
            return Array.from({ length: firstDay }, (_, i) => i);
        },

        get daysInMonth() {
            const totalDays = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
            return Array.from({ length: totalDays }, (_, i) => i + 1);
        },

        formatDateStr(day) {
            const yr = this.currentYear;
            const mo = String(this.currentMonth + 1).padStart(2, '0');
            const dy = String(day).padStart(2, '0');
            return `${yr}-${mo}-${dy}`;
        },

        changeMonth(step) {
            let nextM = this.currentMonth + step;
            let nextY = this.currentYear;
            if (nextM < 0) {
                nextM = 11;
                nextY -= 1;
            } else if (nextM > 11) {
                nextM = 0;
                nextY += 1;
            }
            if (nextY > today.getFullYear() || (nextY === today.getFullYear() && nextM > today.getMonth())) {
                return;
            }
            this.currentMonth = nextM;
            this.currentYear = nextY;
        },

        changeYear(step) {
            let nextY = this.currentYear + step;
            if (nextY > today.getFullYear()) {
                return;
            }
            if (nextY === today.getFullYear() && this.currentMonth > today.getMonth()) {
                this.currentMonth = today.getMonth();
            }
            this.currentYear = nextY;
        },

        togglePicker() {
            this.open = !this.open;
            if (this.open) {
                this.hoverDate = null;
                if (this.from) {
                    const parts = this.from.split('-');
                    if (parts.length === 3) {
                        this.currentYear = parseInt(parts[0], 10);
                        this.currentMonth = parseInt(parts[1], 10) - 1;
                    }
                }
            }
        },

        selectDate(day) {
            const dateStr = this.formatDateStr(day);
            if (dateStr > this.todayStr) return;

            if (!this.from || (this.from && this.to)) {
                this.from = dateStr;
                this.to = '';
                this.hoverDate = null;
            } else if (this.from && !this.to) {
                if (dateStr < this.from) {
                    this.to = this.from;
                    this.from = dateStr;
                } else {
                    this.to = dateStr;
                }
                this.hoverDate = null;
                this.open = false;
            }
        },

        onHoverDay(day) {
            if (this.from && !this.to) {
                const dateStr = this.formatDateStr(day);
                if (dateStr <= this.todayStr) {
                    this.hoverDate = dateStr;
                }
            }
        },

        clearDate() {
            this.from = '';
            this.to = '';
            this.hoverDate = null;
        },

        applySelection() {
            if (this.from && !this.to) {
                this.to = this.from;
            }
            this.open = false;
            this.$el.closest('form').submit();
        },

        getDayState(day) {
            const dateStr = this.formatDateStr(day);
            if (dateStr > this.todayStr) {
                return 'disabled';
            }

            let start = this.from;
            let end = this.to;

            if (this.from && !this.to && this.hoverDate) {
                if (this.hoverDate >= this.from) {
                    start = this.from;
                    end = this.hoverDate;
                } else {
                    start = this.hoverDate;
                    end = this.from;
                }
            }

            if (start && end) {
                if (dateStr === start && dateStr === end) {
                    return 'single';
                }
                if (dateStr === start) {
                    return 'range-start';
                }
                if (dateStr === end) {
                    return 'range-end';
                }
                if (dateStr > start && dateStr < end) {
                    return 'in-range';
                }
            } else if (start && !end) {
                if (dateStr === start) {
                    return 'single';
                }
            }

            return 'available';
        },

        formatDisplayDate(dStr) {
            if (!dStr) return '';
            const parts = dStr.split('-');
            if (parts.length !== 3) return dStr;
            const yr = parts[0];
            const mo = parseInt(parts[1], 10) - 1;
            const dy = parseInt(parts[2], 10);
            return `${dy} ${this.shortMonthNames[mo]} ${yr}`;
        },

        get displayLabel() {
            if (!this.from && !this.to) {
                return 'Pilih Tanggal';
            }
            if (this.from && !this.to) {
                return `${this.formatDisplayDate(this.from)} - ...`;
            }
            if (this.from === this.to) {
                return this.formatDisplayDate(this.from);
            }
            const fromParts = this.from.split('-');
            const toParts = this.to.split('-');
            if (fromParts.length === 3 && toParts.length === 3) {
                const fy = fromParts[0], fm = parseInt(fromParts[1], 10) - 1, fd = parseInt(fromParts[2], 10);
                const ty = toParts[0], tm = parseInt(toParts[1], 10) - 1, td = parseInt(toParts[2], 10);
                if (fy === ty && fm === tm) {
                    return `${fd} - ${td} ${this.shortMonthNames[tm]} ${ty}`;
                }
            }
            return `${this.formatDisplayDate(this.from)} - ${this.formatDisplayDate(this.to)}`;
        }
    };
}
</script>
@endsection
