@extends('layouts.app')

@section('title', 'Impor Produk')
@section('page_title', 'Impor Produk Massal')
@section('page_subtitle', 'Unggah banyak produk sekaligus lewat berkas Excel atau CSV.')

@section('content')
<div class="space-y-6">

    {{-- Tombol kembali --}}
    <a href="{{ route('admin.products') }}"
       class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 hover:text-gray-800 transition">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke Daftar Produk
    </a>

    @if(session('error'))
        <div class="flash-alert p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center gap-3 shadow-sm">
            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="flash-alert p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm shadow-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════ --}}
    {{-- TAHAP 1: Unggah                                --}}
    {{-- ══════════════════════════════════════════════ --}}
    @unless($preview)

        <form action="{{ route('admin.products.import.preview') }}" method="POST" enctype="multipart/form-data"
              x-data="{ fileName: null, dragging: false }">
            @csrf

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <h2 class="text-sm font-black text-gray-800 uppercase tracking-wide">Unggah Berkas</h2>

                    <a href="{{ route('admin.products.import.template') }}"
                       class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-sm transition flex items-center gap-2 shrink-0">
                        <i class="fa-solid fa-download"></i>
                        Unduh Template
                    </a>
                </div>

                <div class="p-6 space-y-5">

                    {{-- Area drag & drop --}}
                    <label for="importFile"
                           @dragover.prevent="dragging = true"
                           @dragleave.prevent="dragging = false"
                           @drop.prevent="dragging = false; $refs.input.files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0]?.name"
                           class="block border-2 border-dashed rounded-2xl p-10 text-center cursor-pointer transition"
                           :class="dragging ? 'border-orange-500 bg-orange-50' : (fileName ? 'border-emerald-300 bg-emerald-50/50' : 'border-gray-200 hover:border-orange-400 hover:bg-orange-50/30')">

                        <template x-if="!fileName">
                            <div>
                                <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-300 mb-3 block"></i>
                                <p class="text-sm font-bold text-gray-600">Tarik berkas ke sini, atau klik untuk memilih</p>
                                <p class="text-xs text-gray-400 mt-1.5">.xlsx, .xls, atau .csv — maksimal 10MB dan {{ $maxRows }} baris</p>
                            </div>
                        </template>

                        <template x-if="fileName">
                            <div>
                                <i class="fa-solid fa-file-circle-check text-4xl text-emerald-500 mb-3 block"></i>
                                <p class="text-sm font-black text-gray-800" x-text="fileName"></p>
                                <p class="text-xs text-gray-400 mt-1.5">Klik untuk mengganti berkas</p>
                            </div>
                        </template>

                        <input type="file" name="file" id="importFile" x-ref="input"
                               accept=".xlsx,.xls,.csv"
                               class="hidden" required
                               @change="fileName = $event.target.files[0]?.name">
                    </label>

                    {{-- Pilihan penanganan duplikat --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2.5">Kalau nama produk sudah ada</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="duplicate_mode" value="skip" checked class="peer sr-only">
                                <div class="p-4 rounded-xl border-2 border-gray-200 peer-checked:border-orange-500 peer-checked:bg-orange-50 transition">
                                    <p class="text-xs font-black text-gray-800">
                                        <i class="fa-solid fa-forward mr-1.5 text-gray-400"></i>Lewati
                                    </p>
                                    <p class="text-[11px] text-gray-500 mt-1">Produk lama dibiarkan apa adanya.</p>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="duplicate_mode" value="update" class="peer sr-only">
                                <div class="p-4 rounded-xl border-2 border-gray-200 peer-checked:border-orange-500 peer-checked:bg-orange-50 transition">
                                    <p class="text-xs font-black text-gray-800">
                                        <i class="fa-solid fa-rotate mr-1.5 text-gray-400"></i>Perbarui
                                    </p>
                                    <p class="text-[11px] text-gray-500 mt-1">Harga, stok, dan varian ditimpa isi berkas.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-black text-xs px-6 py-3.5 rounded-xl shadow transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-magnifying-glass-chart"></i>
                        Periksa Berkas & Lihat Pratinjau
                    </button>

                    <p class="text-[11px] text-gray-400 text-center">
                        <i class="fa-solid fa-shield-halved mr-1"></i>
                        Tidak ada produk yang tersimpan sebelum kamu menekan tombol konfirmasi.
                    </p>
                </div>
            </div>
        </form>

    @else
        {{-- ══════════════════════════════════════════════ --}}
        {{-- TAHAP 2: Pratinjau                             --}}
        {{-- ══════════════════════════════════════════════ --}}

        @if($preview['headerError'])
            <div class="bg-white rounded-2xl border-2 border-red-200 shadow-sm p-8 text-center">
                <i class="fa-solid fa-file-circle-xmark text-4xl text-red-400 mb-4 block"></i>
                <h3 class="text-sm font-black text-gray-800 mb-2">Berkas Tidak Bisa Diproses</h3>
                <p class="text-xs text-gray-500 max-w-lg mx-auto leading-relaxed">{{ $preview['headerError'] }}</p>

                <div class="flex items-center justify-center gap-3 mt-6">
                    <a href="{{ route('admin.products.import.template') }}"
                       class="text-xs font-bold px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white shadow transition">
                        <i class="fa-solid fa-download mr-1.5"></i>Unduh Template
                    </a>
                    <form action="{{ route('admin.products.import.cancel') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs font-bold px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                            Coba Berkas Lain
                        </button>
                    </form>
                </div>
            </div>
        @else
            @php $s = $preview['summary']; @endphp

            {{-- Ringkasan --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-5">
                    <div class="flex items-center gap-3">
                        <span class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-file-circle-check"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-black text-gray-800">{{ $fileName ?? 'Berkas' }}</h3>
                            <p class="text-[11px] text-gray-400">{{ $s['total'] }} baris terbaca · duplikat akan {{ $mode === 'update' ? 'diperbarui' : 'dilewati' }}</p>
                        </div>
                    </div>

                    @if($s['total'] >= $maxRows)
                        <span class="text-[10px] font-black uppercase px-3 py-1.5 rounded-lg bg-amber-100 text-amber-700">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>Dibatasi {{ $maxRows }} baris pertama
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-center">
                        <p class="text-2xl font-black text-emerald-700">{{ $s['ok'] }}</p>
                        <p class="text-[10px] text-emerald-600 font-black uppercase mt-1">Produk Baru</p>
                    </div>
                    <div class="p-4 rounded-xl bg-blue-50 border border-blue-100 text-center">
                        <p class="text-2xl font-black text-blue-700">{{ $s['update'] }}</p>
                        <p class="text-[10px] text-blue-600 font-black uppercase mt-1">Diperbarui</p>
                    </div>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-100 text-center">
                        <p class="text-2xl font-black text-gray-600">{{ $s['skip'] }}</p>
                        <p class="text-[10px] text-gray-500 font-black uppercase mt-1">Dilewati</p>
                    </div>
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-100 text-center">
                        <p class="text-2xl font-black text-amber-700">{{ $s['warning'] }}</p>
                        <p class="text-[10px] text-amber-600 font-black uppercase mt-1">Perlu Dicek</p>
                    </div>
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-100 text-center">
                        <p class="text-2xl font-black text-rose-700">{{ $s['error'] }}</p>
                        <p class="text-[10px] text-rose-600 font-black uppercase mt-1">Bermasalah</p>
                    </div>
                </div>
            </div>

            {{-- Kategori baru --}}
            @if(!empty($preview['newCategories']))
                <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800">
                    <p class="text-xs font-black uppercase tracking-wide mb-2">
                        <i class="fa-solid fa-folder-plus mr-1.5"></i>
                        {{ count($preview['newCategories']) }} Kategori Baru Akan Dibuat Otomatis
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($preview['newCategories'] as $newCategory)
                            <span class="text-xs font-bold px-3 py-1.5 rounded-lg bg-white border border-blue-200">{{ $newCategory }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Varian yang namanya tidak cocok --}}
            @if(!empty($preview['orphanVariants']))
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                    <p class="font-black uppercase tracking-wide mb-2">
                        <i class="fa-solid fa-link-slash mr-1.5"></i>Varian Tanpa Produk
                    </p>
                    <p class="leading-relaxed mb-2">
                        Nama produk berikut ada di sheet <strong>Varian</strong> tapi tidak ditemukan di sheet <strong>Produk</strong>.
                        Variannya tidak akan terpasang. Pastikan nama produknya ditulis persis sama.
                    </p>
                    <ul class="space-y-1">
                        @foreach($preview['orphanVariants'] as $orphan)
                            <li class="font-semibold">
                                "{{ $orphan['name'] }}" — {{ $orphan['count'] }} varian (baris {{ implode(', ', array_slice($orphan['lines'], 0, 8)) }})
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($s['error'] > 0)
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                    <span class="leading-relaxed">
                        <strong>{{ $s['error'] }} baris bermasalah dan akan dilewati.</strong>
                        Baris yang sehat tetap bisa diimpor sekarang.
                    </span>
                </div>
            @endif

            {{-- Tabel pratinjau --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                 x-data="{ filter: 'all', openRow: null }">

                <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <h4 class="text-sm font-black text-gray-800 uppercase tracking-wide">Pratinjau Baris</h4>

                    <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl">
                        @foreach([['all', 'Semua', $s['total']], ['problem', 'Bermasalah', $s['error']], ['warning', 'Perlu Dicek', $s['warning']]] as [$key, $label, $count])
                            <button type="button" @click="filter = '{{ $key }}'"
                                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5"
                                    :class="filter === '{{ $key }}' ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                                {{ $label }}
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-gray-200 text-gray-600">{{ $count }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase w-16">Baris</th>
                                <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase w-28">Status</th>
                                <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase">Produk</th>
                                <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase">Kategori</th>
                                <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase text-right">Harga</th>
                                <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase text-center">Stok</th>
                                <th class="px-4 py-3 text-[10px] font-black text-gray-400 uppercase text-center">Varian</th>
                                <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($preview['rows'] as $row)
                                @php
                                    $badge = match($row['status']) {
                                        'ok'     => ['Baru',       'bg-emerald-100 text-emerald-700', 'fa-plus'],
                                        'update' => ['Diperbarui', 'bg-blue-100 text-blue-700',       'fa-rotate'],
                                        'skip'   => ['Dilewati',   'bg-gray-100 text-gray-500',       'fa-forward'],
                                        default  => ['Bermasalah', 'bg-rose-100 text-rose-700',       'fa-circle-exclamation'],
                                    };
                                    $hasWarning = !empty($row['warnings']) && $row['status'] !== 'error';
                                    $visible = "filter === 'all'"
                                        . " || (filter === 'problem' && " . ($row['status'] === 'error' ? 'true' : 'false') . ')'
                                        . " || (filter === 'warning' && " . ($hasWarning ? 'true' : 'false') . ')';
                                @endphp

                                <tr class="hover:bg-slate-50/70 transition {{ $row['status'] === 'error' ? 'bg-rose-50/40' : '' }}"
                                    x-show="{{ $visible }}">

                                    <td class="px-4 py-3.5 text-xs font-bold text-gray-400">{{ $row['line'] }}</td>

                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase px-2.5 py-1 rounded-full {{ $badge[1] }}">
                                            <i class="fa-solid {{ $badge[2] }} text-[9px]"></i>{{ $badge[0] }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3.5">
                                        <p class="text-xs font-bold text-gray-800 max-w-xs truncate">{{ $row['data']['name'] ?: '—' }}</p>
                                        @if($row['data']['is_featured'])
                                            <span class="text-[9px] font-black uppercase text-orange-600">★ Unggulan</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3.5">
                                        <span class="text-xs font-semibold text-gray-600">{{ $row['categoryName'] ?: '—' }}</span>
                                        @if($row['categoryIsNew'])
                                            <span class="block text-[9px] font-black uppercase text-blue-600 mt-0.5">
                                                <i class="fa-solid fa-plus text-[8px]"></i> Kategori baru
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3.5 text-right">
                                        <p class="text-xs font-bold text-gray-800">Rp {{ number_format($row['data']['price'], 0, ',', '.') }}</p>
                                        @if($row['data']['original_price'])
                                            <p class="text-[10px] text-gray-400 line-through">Rp {{ number_format($row['data']['original_price'], 0, ',', '.') }}</p>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3.5 text-center text-xs font-bold text-gray-700">{{ $row['data']['stock'] }}</td>

                                    <td class="px-4 py-3.5 text-center">
                                        @if(count($row['variants']) > 0)
                                            <button type="button" @click="openRow = openRow === {{ $row['line'] }} ? null : {{ $row['line'] }}"
                                                    class="text-xs font-black px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                                {{ count($row['variants']) }}
                                                <i class="fa-solid fa-chevron-down text-[8px] ml-0.5"></i>
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-300">—</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-3.5">
                                        @if(!empty($row['errors']))
                                            <ul class="space-y-1">
                                                @foreach($row['errors'] as $error)
                                                    <li class="text-[10px] text-rose-700 font-semibold leading-relaxed">
                                                        <i class="fa-solid fa-xmark mr-1"></i>{{ $error }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @elseif(!empty($row['warnings']))
                                            <ul class="space-y-1">
                                                @foreach($row['warnings'] as $warning)
                                                    <li class="text-[10px] text-amber-700 font-semibold leading-relaxed">
                                                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $warning }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @elseif($row['status'] === 'skip')
                                            <span class="text-[10px] text-gray-400 font-semibold">Produk dengan nama ini sudah ada.</span>
                                        @else
                                            <span class="text-[10px] text-emerald-600 font-semibold">
                                                <i class="fa-solid fa-check mr-1"></i>Siap diimpor
                                            </span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Rincian varian + SKU --}}
                                @if(count($row['variants']) > 0)
                                    <tr x-show="openRow === {{ $row['line'] }}" x-cloak class="bg-slate-50">
                                        <td colspan="8" class="px-6 py-4">
                                            <p class="text-[10px] font-black text-gray-500 uppercase mb-2">Varian & SKU</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($row['variants'] as $variant)
                                                    <div class="bg-white border border-gray-200 rounded-lg px-3 py-2">
                                                        <p class="text-[11px] font-bold text-gray-800">
                                                            {{ $variant['color'] }} · {{ $variant['size'] }}
                                                            <span class="text-gray-400 font-semibold">stok {{ $variant['stock'] }}</span>
                                                        </p>
                                                        <code class="text-[10px] {{ $variant['sku'] ? 'text-indigo-600' : 'text-gray-300' }}">
                                                            {{ $variant['sku'] ?: 'SKU dibuat otomatis' }}
                                                        </code>
                                                        @if($variant['price_adjustment'] != 0)
                                                            <span class="block text-[9px] font-bold text-emerald-600">
                                                                +Rp {{ number_format($variant['price_adjustment'], 0, ',', '.') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Bar konfirmasi --}}
            <div class="sticky bottom-4 z-30">
                <div class="bg-slate-900/95 backdrop-blur text-white rounded-2xl px-5 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xl border border-slate-700">
                    @php $willImport = $s['ok'] + $s['update']; @endphp
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-file-import text-orange-400 text-lg"></i>
                        <div>
                            <p class="text-xs font-black">
                                {{ $willImport }} produk siap diimpor
                                @if(!empty($preview['newCategories']))
                                    <span class="text-slate-300 font-semibold">· {{ count($preview['newCategories']) }} kategori baru</span>
                                @endif
                            </p>
                            <p class="text-[10px] text-slate-400">
                                @if($s['error'] > 0 || $s['skip'] > 0)
                                    {{ $s['error'] + $s['skip'] }} baris tidak akan diproses.
                                @else
                                    Semua baris sehat.
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <form action="{{ route('admin.products.import.cancel') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="text-xs font-bold px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                                Batal
                            </button>
                        </form>

                        <form action="{{ route('admin.products.import.store') }}" method="POST"
                              onsubmit="return confirm('Impor {{ $willImport }} produk sekarang?')">
                            @csrf
                            <input type="hidden" name="duplicate_mode" value="{{ $mode }}">
                            <button type="submit" @disabled($willImport === 0)
                                    class="text-xs font-black px-6 py-2.5 rounded-xl text-white shadow transition
                                        {{ $willImport === 0 ? 'bg-gray-600 cursor-not-allowed' : 'bg-orange-600 hover:bg-orange-700' }}">
                                <i class="fa-solid fa-check mr-1.5"></i>Konfirmasi & Impor Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endunless

</div>
@endsection
