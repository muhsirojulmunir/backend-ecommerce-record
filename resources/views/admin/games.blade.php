@extends('layouts.app')

@section('title', 'Kelola Game & Hadiah Voucher')
@section('page_title', 'Kelola Game')

@section('content')
<div class="space-y-6">

    {{-- Flash Alert --}}
    @if(session('success'))
        <div class="flash-alert flex items-center justify-between bg-green-50 border border-green-200 text-green-800 text-sm px-5 py-3.5 rounded-2xl shadow-sm transition-all duration-500 overflow-hidden">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-green-500 text-base"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-green-600 hover:text-green-800 transition p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="flash-alert flex items-center justify-between bg-red-50 border border-red-200 text-red-800 text-sm px-5 py-3.5 rounded-2xl shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-xmark text-red-500 text-base"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-red-600 hover:text-red-800 transition p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 text-white/5 text-9xl pointer-events-none">
            <i class="fa-solid fa-gamepad"></i>
        </div>
        <div class="relative z-10 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-indigo-300 text-xs font-semibold uppercase tracking-wider mb-3">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                Pusat Gamifikasi Toko
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white mb-2">
                Kelola Game & Hadiah Voucher
            </h1>
            <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                Atur aturan tantangan, aktivasi game, serta alokasi nominal voucher hadiah yang bisa dimenangkan oleh pembeli secara real-time.
            </p>
        </div>
    </div>

    {{-- Daftar Game --}}
    <div class="grid grid-cols-1 gap-6">

        @php
            $stopwatch = $games->firstWhere('game_code', 'stopwatch');
            $swConfig  = $stopwatch?->config ?? [];
            $tiers     = $swConfig['tiers'] ?? [];
            $targetCat = $swConfig['category_id'] ?? null;
        @endphp

        {{-- ── 1. GAME: STOPWATCH 10 DETIK ── --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            {{-- Header Game Card --}}
            <div class="bg-gray-50/80 px-6 sm:px-8 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white text-xl shadow-md shadow-indigo-200">
                        <i class="fa-solid fa-stopwatch"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3 class="text-base sm:text-lg font-bold text-gray-900">
                                {{ $stopwatch?->name ?? 'Stopwatch Challenge (10.00 Detik)' }}
                            </h3>
                            @if($stopwatch?->is_active)
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800">
                                    ● AKTIF
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-gray-200 text-gray-600">
                                    NONAKTIF
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Pengunjung menekan tombol stopwatch dan berusaha berhenti tepat di 10.00 detik.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <form action="{{ route('admin.games.toggle', 'stopwatch') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $stopwatch?->is_active ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' : 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm' }}">
                            @if($stopwatch?->is_active)
                                <i class="fa-solid fa-pause"></i> Matikan Game
                            @else
                                <i class="fa-solid fa-play"></i> Aktifkan Game
                            @endif
                        </button>
                    </form>
                </div>
            </div>

            {{-- Form Pengaturan Stopwatch --}}
            <form action="{{ route('admin.games.update', 'stopwatch') }}" method="POST" class="p-6 sm:p-8 space-y-8">
                @csrf
                @method('PUT')

                {{-- Baris 1: Informasi Dasar & Target Kategori --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Nama Tampilan --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <i class="fa-solid fa-heading text-indigo-500 mr-1"></i>Nama Tampilan Game
                        </label>
                        <input type="text" name="name" value="{{ old('name', $stopwatch?->name ?? 'Stopwatch Challenge (10.00 Detik)') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    </div>

                    {{-- Batas Main Harian --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <i class="fa-solid fa-clock-rotate-left text-amber-500 mr-1"></i>Batas Kesempatan Main
                        </label>
                        <div class="relative">
                            <input type="number" name="daily_limit" value="{{ old('daily_limit', $swConfig['daily_limit'] ?? 1) }}" min="1" max="10" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none transition pr-20">
                            <span class="absolute right-4 top-2.5 text-xs text-gray-400 font-medium">kali / hari</span>
                        </div>
                    </div>

                    {{-- Target Kategori Voucher --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <i class="fa-solid fa-layer-group text-blue-500 mr-1"></i>Alokasi Kategori Voucher
                        </label>
                        <select name="category_id" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                            <option value="">Semua Kategori (Acak dari Semua Stok Ready)</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (string)$targetCat === (string)$cat->id ? 'selected' : '' }}>
                                    Khusus Kategori: {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">
                            Pilih jika voucher yang keluar ingin dibatasi pada kategori sepatu tertentu saja.
                        </p>
                    </div>
                </div>

                {{-- Status Aktif Checkbox --}}
                <div class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-200/80 rounded-2xl">
                    <input type="checkbox" id="is_active_sw" name="is_active" value="1" {{ old('is_active', $stopwatch?->is_active ?? true) ? 'checked' : '' }}
                        class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 cursor-pointer">
                    <label for="is_active_sw" class="text-xs font-bold text-gray-800 cursor-pointer">
                        Aktifkan widget stopwatch di website customer (Pengunjung bisa melihat dan bermain).
                    </label>
                </div>

                {{-- Divider --}}
                <div class="border-t border-gray-100 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-sm font-black text-gray-900 uppercase tracking-wider">
                                Aturan Tingkatan & Hadiah Voucher (3 Tiers)
                            </h4>
                            <p class="text-xs text-gray-500">
                                Voucher akan diambil otomatis dari stok <strong>Kelola Voucher</strong> sesuai nominal tier yang dicapai pemain.
                            </p>
                        </div>
                    </div>

                    {{-- 3 Tier Cards --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                        {{-- TIER 1: PERFECT --}}
                        <div class="p-5 rounded-2xl bg-amber-50/60 border border-amber-200/80 relative space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="px-3 py-1 rounded-full bg-amber-200 text-amber-900 font-extrabold text-[11px] uppercase tracking-wider">
                                    🥇 Tier 1: Sempurna
                                </span>
                                <span class="text-xs font-black text-amber-800 font-mono">10.00s Target</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">
                                    Nominal Hadiah Voucher (Rp)
                                </label>
                                <input type="number" name="tiers[perfect][amount]" value="{{ old('tiers.perfect.amount', $tiers['perfect']['amount'] ?? 50000) }}" step="1000" min="1000" required
                                    class="w-full px-4 py-2 bg-white border border-amber-300 rounded-xl text-sm font-bold text-amber-900 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Detik Min</label>
                                    <input type="number" step="0.01" name="tiers[perfect][min]" value="{{ old('tiers.perfect.min', $tiers['perfect']['min'] ?? 9.98) }}" required
                                        class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold focus:ring-1 focus:ring-amber-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Detik Max</label>
                                    <input type="number" step="0.01" name="tiers[perfect][max]" value="{{ old('tiers.perfect.max', $tiers['perfect']['max'] ?? 10.02) }}" required
                                        class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold focus:ring-1 focus:ring-amber-500">
                                </div>
                            </div>
                            <p class="text-[10px] text-amber-700">Contoh: 9.98s s/d 10.02s dianggap pas 10 detik.</p>
                        </div>

                        {{-- TIER 2: NEAR --}}
                        <div class="p-5 rounded-2xl bg-indigo-50/60 border border-indigo-200/80 relative space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="px-3 py-1 rounded-full bg-indigo-200 text-indigo-900 font-extrabold text-[11px] uppercase tracking-wider">
                                    🥈 Tier 2: Nyaris Pas
                                </span>
                                <span class="text-xs font-black text-indigo-800 font-mono">Hampir 10s</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">
                                    Nominal Hadiah Voucher (Rp)
                                </label>
                                <input type="number" name="tiers[near][amount]" value="{{ old('tiers.near.amount', $tiers['near']['amount'] ?? 30000) }}" step="1000" min="1000" required
                                    class="w-full px-4 py-2 bg-white border border-indigo-300 rounded-xl text-sm font-bold text-indigo-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Detik Min</label>
                                    <input type="number" step="0.01" name="tiers[near][min]" value="{{ old('tiers.near.min', $tiers['near']['min'] ?? 9.85) }}" required
                                        class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold focus:ring-1 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Detik Max</label>
                                    <input type="number" step="0.01" name="tiers[near][max]" value="{{ old('tiers.near.max', $tiers['near']['max'] ?? 10.15) }}" required
                                        class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono font-bold focus:ring-1 focus:ring-indigo-500">
                                </div>
                            </div>
                            <p class="text-[10px] text-indigo-700">Contoh: 9.85s s/d 10.15s (selisih tipis).</p>
                        </div>

                        {{-- TIER 3: GOOD --}}
                        <div class="p-5 rounded-2xl bg-emerald-50/60 border border-emerald-200/80 relative space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="px-3 py-1 rounded-full bg-emerald-200 text-emerald-900 font-extrabold text-[11px] uppercase tracking-wider">
                                    🥉 Tier 3: Partisipasi
                                </span>
                                <span class="text-xs font-black text-emerald-800 font-mono">Semua Hasil Lain</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">
                                    Nominal Hadiah Voucher (Rp)
                                </label>
                                <input type="number" name="tiers[good][amount]" value="{{ old('tiers.good.amount', $tiers['good']['amount'] ?? 10000) }}" step="1000" min="0" required
                                    class="w-full px-4 py-2 bg-white border border-emerald-300 rounded-xl text-sm font-bold text-emerald-900 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>

                            <div class="pt-3">
                                <p class="text-xs text-emerald-800 leading-relaxed">
                                    Diberikan kepada semua pemain yang mencoba namun belum mencapai rentang waktu Tier 1 atau Tier 2. (Isi 0 jika tidak ingin memberi hadiah).
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="submit"
                        class="bg-[#1B3A6B] hover:bg-[#2A4F8F] text-white text-sm font-bold py-3 px-8 rounded-xl transition shadow-md shadow-blue-900/15 flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan Pengaturan Game Stopwatch
                    </button>
                </div>
            </form>
        </div>

        {{-- ── 2. MODUL GAME MASA DEPAN (SPIN WHEEL & LAINNYA) ── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Spin Wheel Slot --}}
            <div class="bg-white rounded-3xl border border-gray-200/70 border-dashed p-6 flex flex-col justify-between shadow-sm">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-dharmachakra"></i>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">
                            Slot Game Berikutnya
                        </span>
                    </div>
                    <h4 class="text-base font-bold text-gray-900 mb-1">
                        Spin Wheel (Roda Putar Keberuntungan)
                    </h4>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Modul game roda putar berhadiah voucher potongan harga dengan probabilitas sektor hadiah yang dapat disesuaikan.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
                    <span>Engine: GameSetting JSON</span>
                    <span class="font-bold text-gray-600">Siap Ditambahkan</span>
                </div>
            </div>

            {{-- Slot Game Tambahan --}}
            <div class="bg-white rounded-3xl border border-gray-200/70 border-dashed p-6 flex flex-col justify-between shadow-sm">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200">
                            Slot Fleksibel
                        </span>
                    </div>
                    <h4 class="text-base font-bold text-gray-900 mb-1">
                        Game Tambahan Baru (Lucky Box / Kuis)
                    </h4>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Database dan controller telah siap menerima modul game mini baru kapan pun Mas ingin menambahkan variasi hiburan untuk customer.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
                    <span>Arsitektur: Multi-Game Modular</span>
                    <span class="font-bold text-gray-600">Terbuka untuk Ekspansi</span>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
