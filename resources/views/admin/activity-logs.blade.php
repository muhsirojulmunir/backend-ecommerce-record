@extends('layouts.app')

@section('title', 'Log Aktivitas')
@section('page_title', 'Log Aktivitas')
@section('page_subtitle', 'Riwayat perubahan data yang dilakukan di Seller Center.')

@section('content')
@php
    // Warna & ikon per jenis aksi
    $eventMeta = [
        'created' => ['Ditambahkan', 'fa-plus',        'bg-emerald-100 text-emerald-700', 'bg-emerald-500'],
        'updated' => ['Diperbarui',  'fa-pen',         'bg-blue-100 text-blue-700',       'bg-blue-500'],
        'deleted' => ['Dihapus',     'fa-trash',       'bg-rose-100 text-rose-700',       'bg-rose-500'],
    ];
    $moduleIcons = [
        'produk'     => 'fa-box-open',
        'pesanan'    => 'fa-receipt',
        'banner'     => 'fa-images',
        'diskon'     => 'fa-tags',
        'kategori'   => 'fa-layer-group',
        'pengguna'   => 'fa-user',
        'pengaturan' => 'fa-gear',
    ];
    $hasFilter = collect($filters)->filter()->isNotEmpty();
@endphp

<div class="space-y-6" x-data="{ showDetail: false, detail: {}, showPrune: false }">

    {{-- ── Statistik ── --}}
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

    {{-- ── Filter ── --}}
    <form action="{{ route('admin.activity-logs') }}" method="GET"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-3 items-end">

            <div class="xl:col-span-2">
                <label class="block text-[10px] font-black text-gray-500 uppercase mb-1.5">Cari</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                           placeholder="Deskripsi atau isi perubahan..."
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
                <label class="block text-[10px] font-black text-gray-500 uppercase mb-1.5">Pelaku</label>
                <select name="causer" class="w-full border border-gray-200 rounded-xl py-2 px-3 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    <option value="">Semua Pelaku</option>
                    @foreach($causers as $user)
                        <option value="{{ $user->id }}" @selected((string) $filters['causer'] === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
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
                <a href="{{ route('admin.activity-logs') }}"
                   class="text-xs font-bold px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                    <i class="fa-solid fa-xmark mr-1"></i>Reset
                </a>
                <span class="text-[11px] text-gray-400 font-semibold ml-auto">{{ number_format($logs->total()) }} catatan cocok</span>
            @endif
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

                        $attributes = $log->properties['attributes'] ?? [];
                        $old        = $log->properties['old'] ?? [];
                        $changeKeys = array_keys($attributes);

                        $payload = [
                            'description' => $log->description,
                            'module'      => ucfirst($log->log_name),
                            'event'       => $evLabel,
                            'causer'      => $log->causer?->name ?? 'Sistem',
                            'causerEmail' => $log->causer?->email ?? '—',
                            'subject'     => class_basename($log->subject_type ?? '') . ($log->subject_id ? ' #' . $log->subject_id : ''),
                            'time'        => $log->created_at?->translatedFormat('l, d F Y · H:i:s'),
                            'ago'         => $log->created_at?->diffForHumans(),
                            'attributes'  => $attributes,
                            'old'         => $old,
                        ];
                    @endphp

                    <div class="px-6 py-4 hover:bg-slate-50/60 transition flex items-start gap-4">

                        {{-- Titik aksi --}}
                        <div class="shrink-0 pt-0.5">
                            <span class="w-9 h-9 rounded-xl {{ $evBadge }} flex items-center justify-center">
                                <i class="fa-solid {{ $evIcon }} text-xs"></i>
                            </span>
                        </div>

                        {{-- Isi --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                    <i class="fa-solid {{ $moduleIcon }} text-[9px]"></i>{{ $log->log_name }}
                                </span>
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full {{ $evBadge }}">{{ $evLabel }}</span>
                                @if($log->subject_id)
                                    <code class="text-[10px] text-gray-400">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</code>
                                @endif
                            </div>

                            <p class="text-xs font-bold text-gray-800 leading-relaxed">{{ ucfirst($log->description) }}</p>

                            {{-- Ringkasan kolom yang berubah --}}
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

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-[10px] text-gray-400 font-semibold">
                                <span>
                                    <i class="fa-solid fa-user mr-1"></i>
                                    {{ $log->causer?->name ?? 'Sistem' }}
                                </span>
                                <span title="{{ $log->created_at }}">
                                    <i class="fa-solid fa-clock mr-1"></i>
                                    {{ $log->created_at?->translatedFormat('d M Y H:i') }} · {{ $log->created_at?->diffForHumans() }}
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
                            <p class="text-xs font-bold text-gray-800 mt-0.5" x-text="detail.causer"></p>
                            <p class="text-[10px] text-gray-400" x-text="detail.causerEmail"></p>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <p class="text-[10px] text-gray-400 font-black uppercase">Data Terkait</p>
                            <p class="text-xs font-bold text-gray-800 mt-0.5" x-text="detail.subject"></p>
                        </div>
                    </div>
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
