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

            <div class="flex gap-2">
                <div class="flex-1">
                    <label class="block text-[10px] font-black text-gray-500 uppercase mb-1.5">Dari</label>
                    <input type="date" name="from" value="{{ $filters['from'] }}"
                           class="w-full border border-gray-200 rounded-xl py-2 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                </div>
                <div class="flex-1">
                    <label class="block text-[10px] font-black text-gray-500 uppercase mb-1.5">Sampai</label>
                    <input type="date" name="to" value="{{ $filters['to'] }}"
                           class="w-full border border-gray-200 rounded-xl py-2 px-2 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-50">
            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold px-5 py-2 rounded-xl transition">
                <i class="fa-solid fa-filter mr-1.5"></i>Terapkan Filter
            </button>
            @if($hasFilter)
                <a href="{{ route('admin.activity-logs', ['tab' => $currentTab]) }}"
                   class="text-xs font-bold px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                    <i class="fa-solid fa-xmark mr-1"></i>Reset
                </a>
            @endif
            <span class="text-[11px] text-gray-400 font-semibold ml-auto">{{ number_format($logs->total()) }} catatan</span>
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

                        $props      = is_array($log->properties) ? $log->properties : $log->properties->toArray();
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
                            'causer'        => $actorInfo['name'],
                            'causerEmail'   => $actorInfo['email'],
                            'causerRole'    => $actorInfo['role_label'],
                            'isGuest'       => $actorInfo['is_guest'],
                            'subject'       => class_basename($log->subject_type ?? '') . ($log->subject_id ? ' #' . $log->subject_id : ''),
                            'time'          => $log->created_at?->translatedFormat('l, d F Y · H:i:s'),
                            'ago'           => $log->created_at?->diffForHumans(),
                            'attributes'    => $attributes,
                            'old'           => $old,
                            'deviceType'    => $device['device_type'],
                            'deviceLabel'   => $device['formatted'],
                            'devicePlatform'=> $device['platform'],
                            'deviceBrowser' => $device['browser'],
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
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $actorInfo['badge_class'] }}">
                                    <i class="fa-solid {{ $actorInfo['icon'] }} text-[9px]"></i>
                                    {{ $actorInfo['name'] }}
                                    @if($actorInfo['is_guest'])
                                        <span class="opacity-70">· Tamu</span>
                                    @endif
                                </span>
                                {{-- Device badge --}}
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $device['badge_class'] }} border">
                                    <i class="fa-solid {{ $device['icon'] }} text-[9px]"></i>
                                    {{ $device['device_type'] }}
                                    @if(!empty($props['ip']))
                                        <span class="opacity-60">· {{ $props['ip'] }}</span>
                                    @endif
                                </span>
                                {{-- Browser & Platform --}}
                                <span class="text-[10px] text-gray-400 font-semibold">
                                    {{ $device['platform'] }} · {{ $device['browser'] }}
                                </span>
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
@endsection
