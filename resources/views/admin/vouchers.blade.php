@extends('layouts.app')

@section('title', 'Kelola Voucher')
@section('page_title', 'Kelola Voucher')

@section('content')
<div class="space-y-6" x-data="voucherManager()">

    {{-- Flash Alerts --}}
    @if(session('success'))
        <div class="flash-alert flex items-center justify-between bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm px-5 py-3.5 rounded-xl shadow-xs transition-all duration-300">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-emerald-600 hover:text-emerald-800 transition p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="flash-alert flex items-center justify-between bg-rose-50 border border-rose-200 text-rose-900 text-sm px-5 py-3.5 rounded-xl shadow-xs">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-xmark text-rose-600 text-base"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-rose-600 hover:text-rose-800 transition p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    {{-- ── 1. STATISTIK CARDS (Theme: Navy & Orange) ── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
        {{-- Total --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Voucher</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-700">
                    <i class="fa-solid fa-ticket text-sm"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ number_format($stats['total']) }}</p>
        </div>

        {{-- Promo Massal (RECORD) --}}
        <div class="bg-white rounded-xl border border-orange-200 p-4 shadow-xs bg-gradient-to-br from-white to-orange-50/40">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-orange-700">Promo Massal</span>
                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600">
                    <i class="fa-solid fa-bullhorn text-sm"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-orange-600">{{ number_format($stats['promo'] ?? 0) }}</p>
        </div>

        {{-- Tersedia --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tersedia</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i class="fa-solid fa-circle-check text-sm"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-emerald-600">{{ number_format($stats['available']) }}</p>
        </div>

        {{-- Klaim Game --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Klaim Game</span>
                <div class="w-8 h-8 rounded-lg bg-sky-50 flex items-center justify-center text-sky-600">
                    <i class="fa-solid fa-gamepad text-sm"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-sky-700">{{ number_format($stats['claimed'] ?? 0) }}</p>
        </div>

        {{-- Terpakai --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Terpakai</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                    <i class="fa-solid fa-receipt text-sm"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-amber-600">{{ number_format($stats['used']) }}</p>
        </div>

        {{-- Kadaluarsa --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Kadaluarsa</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600">
                    <i class="fa-solid fa-clock text-sm"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-rose-600">{{ number_format($stats['expired']) }}</p>
        </div>
    </div>

    {{-- ── 2. FORM PEMBUATAN VOUCHER (Clean Navy & Orange Accent) ── --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        {{-- Header Bar Navy --}}
        <div class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-white font-bold text-sm flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                    <span>Buat Voucher Belanja</span>
                </h3>
                <p class="text-slate-400 text-xs mt-0.5">Atur kode promo toko atau buat kupon acak secara terstruktur.</p>
            </div>

            {{-- Segmented Tabs --}}
            <div class="inline-flex p-1 bg-slate-950/80 rounded-lg border border-slate-800">
                <button type="button" @click="formTab = 'promo'"
                    :class="formTab === 'promo' ? 'bg-orange-500 text-white shadow-xs font-bold' : 'text-slate-400 hover:text-white font-medium'"
                    class="px-4 py-1.5 rounded-md text-xs transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                    <span>Voucher Promo (RECORD)</span>
                </button>
                <button type="button" @click="formTab = 'batch'"
                    :class="formTab === 'batch' ? 'bg-orange-500 text-white shadow-xs font-bold' : 'text-slate-400 hover:text-white font-medium'"
                    class="px-4 py-1.5 rounded-md text-xs transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-ticket text-xs"></i>
                    <span>Generate Kupon Batch Acak</span>
                </button>
            </div>
        </div>

        {{-- ── TAB 1: FORM VOUCHER PROMO (RECORD) ── --}}
        <div x-show="formTab === 'promo'" class="p-6">
            <form action="{{ route('admin.vouchers.promo.store') }}" method="POST">
                @csrf

                {{-- Anti-Fraud Security Notice --}}
                <div class="mb-5 bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-orange-50 border border-orange-200 flex items-center justify-center text-orange-600 shrink-0 mt-0.5">
                        <i class="fa-solid fa-shield-halved text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800">Proteksi Anti-Kecurangan (Anti-Fraud Device Lock) Aktif Otomatis:</p>
                        <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                            Kode promo ini dapat digunakan oleh banyak pelanggan berbeda, namun sistem secara ketat membatasi maksimal <strong>1x penggunaan per Akun</strong> dan <strong>1x penggunaan per Perangkat Fisik (Device Fingerprint)</strong>. Pengguna yang membuat banyak akun di HP/Laptop yang sama akan otomatis terblokir.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    {{-- Kode Promo --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Kode Voucher Promo <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="code" value="{{ old('code', 'RECORD') }}" required maxlength="20"
                            placeholder="Contoh: RECORD"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm font-mono font-bold uppercase tracking-wider text-slate-900 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition @error('code') border-rose-400 @enderror">
                        @error('code')
                            <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nominal Diskon --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Nominal Potongan <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative flex rounded-lg">
                            <span class="inline-flex items-center px-3 bg-slate-100 border border-r-0 border-slate-300 rounded-l-lg text-xs font-bold text-slate-600">Rp</span>
                            <input type="text"
                                   x-ref="displayPromoAmountInput"
                                   :value="displayPromoAmount"
                                   @input="formatPromoAmount($event.target.value)"
                                   value="{{ old('amount') ? number_format((int)old('amount'), 0, ',', '.') : '50.000' }}"
                                   placeholder="50.000"
                                   required
                                   autocomplete="off"
                                   class="w-full px-3 py-2.5 border border-slate-300 rounded-r-lg text-sm font-semibold text-slate-900 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition @error('amount') border-rose-400 @enderror">
                        </div>
                        <input type="hidden" name="amount" :value="rawPromoAmount" value="{{ old('amount', 50000) }}">
                        @error('amount')
                            <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kuota Pemakaian --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Batas Total Kuota <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input type="number" name="total_quota" value="{{ old('total_quota') }}" min="1" placeholder="Kosongkan jika unlimited"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition">
                        <p class="text-[10px] text-slate-400 mt-1">Contoh: 100 orang pertama</p>
                    </div>

                    {{-- Kadaluarsa --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Masa Berlaku <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition @error('expires_at') border-rose-400 @enderror">
                        @error('expires_at')
                            <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Target Kategori --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Kategori Produk
                        </label>
                        <select name="category_id" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition">
                            <option value="">Semua Kategori (Universal)</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div class="text-xs text-slate-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-lock text-slate-400 text-xs"></i>
                        <span>Aturan terkunci otomatis: <strong>1x per Akun</strong> & <strong>1x per Perangkat</strong>.</span>
                    </div>

                    <button type="submit"
                        class="bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-xs font-bold py-2.5 px-6 rounded-lg transition shadow-xs flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        Simpan Voucher Promo
                    </button>
                </div>
            </form>
        </div>

        {{-- ── TAB 2: FORM GENERATE BATCH ACAK ── --}}
        <div x-show="formTab === 'batch'" class="p-6">
            <form action="{{ route('admin.vouchers.generate') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    {{-- Jumlah --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Jumlah Kupon <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="quantity" value="{{ old('quantity', 10) }}" min="1" max="500" required
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition @error('quantity') border-rose-400 @enderror">
                        @error('quantity')
                            <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nominal --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Nominal (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative flex rounded-lg">
                            <span class="inline-flex items-center px-3 bg-slate-100 border border-r-0 border-slate-300 rounded-l-lg text-xs font-bold text-slate-600">Rp</span>
                            <input type="text"
                                   x-ref="displayAmountInput"
                                   :value="displayAmount"
                                   @input="formatAmount($event.target.value)"
                                   value="{{ old('amount') ? number_format((int)old('amount'), 0, ',', '.') : '50.000' }}"
                                   placeholder="50.000"
                                   required
                                   autocomplete="off"
                                   class="w-full px-3 py-2.5 border border-slate-300 rounded-r-lg text-sm font-semibold text-slate-900 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition @error('amount') border-rose-400 @enderror">
                        </div>
                        <input type="hidden" name="amount" id="voucher_raw_amount" :value="rawAmount" value="{{ old('amount', 50000) }}">
                        @error('amount')
                            <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kadaluarsa --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Kadaluarsa <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition @error('expires_at') border-rose-400 @enderror">
                        @error('expires_at')
                            <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Label Batch --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Label Batch <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input type="text" name="batch_label" value="{{ old('batch_label') }}" placeholder="Auto-generate jika kosong" maxlength="100"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition placeholder-slate-400">
                    </div>

                    {{-- Target Kategori --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Target Kategori
                        </label>
                        <select name="category_id" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-900 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition">
                            <option value="">Semua Kategori (Universal)</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-400">
                        <i class="fa-solid fa-circle-info mr-1 text-slate-400"></i>
                        Kupon acak 4 karakter sekali pakai untuk cetak fisik F4 atau reward game stopwatch.
                    </p>

                    <button type="submit"
                        class="bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white text-xs font-bold py-2.5 px-6 rounded-lg transition shadow-xs flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-ticket text-xs text-orange-400"></i>
                        Generate Kupon Batch
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── 3. FILTER & PENCARIAN ── --}}
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-4">
        <form action="{{ route('admin.vouchers') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Kode</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik kode voucher..."
                        class="w-full pl-8 pr-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition uppercase font-mono">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tipe</label>
                <select name="type" class="px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition bg-white">
                    <option value="">Semua Tipe</option>
                    <option value="promo" {{ request('type') === 'promo' ? 'selected' : '' }}>Promo Massal (RECORD)</option>
                    <option value="single" {{ request('type') === 'single' ? 'selected' : '' }}>Kupon Acak (Single-Use)</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition bg-white">
                    <option value="">Semua Status</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia / Aktif</option>
                    <option value="claimed" {{ request('status') === 'claimed' ? 'selected' : '' }}>Diklaim User (Game)</option>
                    <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>Terpakai (Checkout)</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Kadaluarsa</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori</label>
                <select name="category_id" class="px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition bg-white max-w-[170px]">
                    <option value="">Semua Kategori</option>
                    <option value="all" {{ request('category_id') === 'all' ? 'selected' : '' }}>Universal (Semua)</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Batch</label>
                <select name="batch" class="px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition bg-white max-w-[190px]">
                    <option value="">Semua Batch</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch }}" {{ request('batch') === $batch ? 'selected' : '' }}>{{ $batch }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold py-2 px-4 rounded-lg transition shadow-xs cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    <span>Filter</span>
                </button>
                <a href="{{ route('admin.vouchers') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold py-2 px-3 rounded-lg transition flex items-center gap-1">
                    <i class="fa-solid fa-rotate text-[10px]"></i>
                    <span>Reset</span>
                </a>
            </div>
        </form>
    </div>

    {{-- ── 4. AKSI BATCH (CETAK / HAPUS) ── --}}
    @if(request('batch'))
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.vouchers.print', ['batch' => request('batch')]) }}" target="_blank"
                class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold py-2 px-4 rounded-lg transition shadow-xs flex items-center gap-2">
                <i class="fa-solid fa-print text-orange-400"></i>
                <span>Cetak Batch Ini (F4)</span>
            </a>

            <form action="{{ route('admin.vouchers.destroy') }}" method="POST"
                onsubmit="return confirm('Yakin hapus semua voucher BELUM TERPAKAI di batch ini?')">
                @csrf
                @method('DELETE')
                <input type="hidden" name="batch_label" value="{{ request('batch') }}">
                <button type="submit"
                    class="bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold py-2 px-4 rounded-lg transition border border-rose-200 flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                    <span>Hapus Batch Belum Terpakai</span>
                </button>
            </form>
        </div>
    @endif

    {{-- ── 5. TABEL VOUCHER (Clean Table Style) ── --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="bg-slate-50/90 text-slate-500 uppercase border-b border-slate-200 text-[10px] tracking-wider font-extrabold">
                        <th class="w-10 px-4 py-3 text-center">
                            <input type="checkbox"
                                   @click="toggleSelectAll()"
                                   :checked="isAllSelected"
                                   class="w-3.5 h-3.5 text-orange-600 rounded border-slate-300 focus:ring-orange-500 cursor-pointer">
                        </th>
                        <th class="px-4 py-3">Kode Voucher</th>
                        <th class="px-4 py-3">Nominal Diskon</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Batch / Label</th>
                        <th class="px-4 py-3">Status / Kuota</th>
                        <th class="px-4 py-3">Masa Berlaku</th>
                        <th class="px-4 py-3">Dipakai Oleh / Log Perangkat</th>
                        <th class="px-4 py-3">Dibuat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($vouchers as $v)
                        <tr class="hover:bg-slate-50/70 transition" :class="{ 'bg-orange-50/30': selectedVouchers.includes({{ $v->id }}) }">
                            {{-- Checkbox --}}
                            <td class="w-10 px-4 py-3.5 text-center" @click.stop>
                                <input type="checkbox"
                                       :value="{{ $v->id }}"
                                       x-model.number="selectedVouchers"
                                       class="w-3.5 h-3.5 text-orange-600 rounded border-slate-300 focus:ring-orange-500 cursor-pointer">
                            </td>

                            {{-- Kode --}}
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center bg-slate-900 text-white font-mono font-bold text-xs px-2.5 py-1 rounded-md tracking-wider select-all shadow-2xs">
                                        {{ $v->code }}
                                    </span>
                                    @if($v->isPromo())
                                        <span class="inline-flex items-center gap-1 bg-orange-100 text-orange-800 border border-orange-200 text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded">
                                            <i class="fa-solid fa-shield-halved text-[8px]"></i> Promo
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Nominal --}}
                            <td class="px-4 py-3.5">
                                <span class="font-extrabold text-slate-900 text-xs">{{ $v->formatted_amount }}</span>
                            </td>

                            {{-- Kategori --}}
                            <td class="px-4 py-3.5">
                                @if($v->category)
                                    <span class="inline-flex items-center gap-1 text-slate-700 font-semibold text-[11px] bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                        <i class="fa-solid fa-layer-group text-[9px] text-slate-500"></i>
                                        {{ $v->category->name }}
                                    </span>
                                @else
                                    <span class="text-slate-500 text-[11px]">Semua Produk</span>
                                @endif
                            </td>

                            {{-- Batch --}}
                            <td class="px-4 py-3.5">
                                @if($v->batch_label)
                                    <a href="{{ route('admin.vouchers', ['batch' => $v->batch_label]) }}"
                                        class="text-xs text-slate-700 hover:text-orange-600 font-medium hover:underline transition">
                                        {{ \Illuminate\Support\Str::limit($v->batch_label, 20) }}
                                    </a>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>

                            {{-- Status / Kuota --}}
                            <td class="px-4 py-3.5">
                                @if($v->isPromo())
                                    @if($v->is_expired)
                                        <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 text-[10px] font-bold px-2 py-0.5 rounded border border-rose-200">
                                            <i class="fa-solid fa-clock text-[9px]"></i> Kadaluarsa
                                        </span>
                                    @elseif($v->total_quota !== null && $v->used_count >= $v->total_quota)
                                        <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded border border-amber-200">
                                            <i class="fa-solid fa-ban text-[9px]"></i> Kuota Habis
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded border border-emerald-200">
                                            <i class="fa-solid fa-circle-check text-[9px]"></i> Aktif
                                        </span>
                                    @endif
                                    <p class="text-[10px] text-slate-500 font-medium mt-1">
                                        Kuota: <strong class="text-slate-900">{{ $v->used_count }} / {{ $v->total_quota ? number_format($v->total_quota) : '∞' }}</strong>
                                    </p>
                                @elseif($v->is_used)
                                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded border border-emerald-200">
                                        <i class="fa-solid fa-circle-check text-[9px]"></i> Terpakai
                                    </span>
                                @elseif($v->used_by && $v->usedByUser)
                                    <span class="inline-flex items-center gap-1 bg-sky-50 text-sky-700 text-[10px] font-bold px-2 py-0.5 rounded border border-sky-200" title="Disimpan oleh user dari game">
                                        <i class="fa-solid fa-gamepad text-[9px]"></i> Klaim Game
                                    </span>
                                @elseif($v->reserved_for && $v->reserved_until && $v->reserved_until->isFuture())
                                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded border border-amber-200" title="Sedang dimainkan user">
                                        <i class="fa-solid fa-stopwatch text-[9px]"></i> Sesi Game
                                    </span>
                                @elseif($v->is_expired)
                                    <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 text-[10px] font-bold px-2 py-0.5 rounded border border-rose-200">
                                        <i class="fa-solid fa-clock text-[9px]"></i> Kadaluarsa
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 text-[10px] font-bold px-2 py-0.5 rounded border border-slate-200">
                                        <i class="fa-solid fa-circle text-[7px] text-emerald-500"></i> Tersedia
                                    </span>
                                @endif
                            </td>

                            {{-- Kadaluarsa --}}
                            <td class="px-4 py-3.5 text-slate-600">
                                {{ $v->expires_at ? $v->expires_at->format('d/m/Y') : '—' }}
                            </td>

                            {{-- Dipakai Oleh / Log Perangkat --}}
                            <td class="px-4 py-3.5">
                                @if($v->isPromo())
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="openUsagesModal({{ $v->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-white hover:bg-orange-50 text-slate-700 hover:text-orange-700 border border-slate-200 hover:border-orange-300 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer">
                                            <i class="fa-solid fa-clock-rotate-left text-xs text-orange-500"></i>
                                            <span>Riwayat ({{ $v->used_count }})</span>
                                        </button>
                                        <span class="text-[10px] text-slate-400">1x Device</span>
                                    </div>
                                @elseif($v->is_used && $v->usedByUser)
                                    <div>
                                        <p class="font-bold text-slate-900 flex items-center gap-1">
                                            <i class="fa-solid fa-user-check text-emerald-600 text-[10px]"></i>
                                            {{ $v->usedByUser->name }}
                                        </p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">
                                            @if($v->order)
                                                Order #{{ $v->order->order_number ?? $v->order->id }} • 
                                            @endif
                                            {{ $v->used_at?->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                @elseif($v->used_by && $v->usedByUser)
                                    <div>
                                        <p class="font-bold text-sky-800 flex items-center gap-1">
                                            <i class="fa-solid fa-user text-sky-600 text-[10px]"></i>
                                            {{ $v->usedByUser->name }}
                                        </p>
                                        <p class="text-[10px] text-sky-600 mt-0.5 font-medium">Tersimpan di akun</p>
                                    </div>
                                @elseif($v->reserved_for && $v->reservedByUser && $v->reserved_until && $v->reserved_until->isFuture())
                                    <div>
                                        <p class="font-bold text-amber-800 flex items-center gap-1">
                                            <i class="fa-solid fa-stopwatch text-amber-600 text-[10px]"></i>
                                            {{ $v->reservedByUser->name }}
                                        </p>
                                        <p class="text-[10px] text-amber-600 mt-0.5">Sesi aktif ({{ $v->reserved_until->diffForHumans() }})</p>
                                    </div>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>

                            {{-- Dibuat --}}
                            <td class="px-4 py-3.5 text-slate-500">
                                {{ $v->created_at->format('d/m/Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <i class="fa-solid fa-ticket text-3xl text-slate-300"></i>
                                    <p class="text-xs font-bold text-slate-600">Belum ada voucher</p>
                                    <p class="text-[11px] text-slate-400">Buat voucher promo atau kupon pertama Anda pada form di atas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginasi --}}
        @if($vouchers->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>

    {{-- ── 6. MODAL RIWAYAT PEMAKAIAN VOUCHER (Clean Navy & Orange) ── --}}
    <div x-cloak x-show="showUsagesModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showUsagesModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"
                 @click="showUsagesModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showUsagesModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-3 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-3 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-200">
                
                {{-- Modal Header --}}
                <div class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex items-center justify-between text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-orange-500/20 border border-orange-500/40 flex items-center justify-center text-orange-400">
                            <i class="fa-solid fa-shield-halved text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm flex items-center gap-2">
                                <span>Log Pemakaian Voucher:</span>
                                <span class="text-orange-400 font-mono font-black tracking-wider text-base" x-text="currentVoucher.code"></span>
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Aturan: 1x Perangkat & 1x Akun • Terpakai: <span class="font-bold text-white" x-text="currentVoucher.quota_label"></span>
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showUsagesModal = false" class="text-slate-400 hover:text-white transition p-1.5 rounded-lg hover:bg-slate-800 cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6">
                    <div x-show="loadingUsages" class="py-12 text-center text-slate-400">
                        <i class="fa-solid fa-circle-notch fa-spin text-2xl text-orange-500 mb-2.5"></i>
                        <p class="text-xs font-semibold text-slate-600">Memuat log pemakaian perangkat...</p>
                    </div>

                    <div x-show="!loadingUsages && currentUsages.length === 0" class="py-12 text-center text-slate-400">
                        <i class="fa-solid fa-clipboard-list text-3xl text-slate-300 mb-2"></i>
                        <p class="text-xs font-bold text-slate-700">Belum Ada Pemakaian</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Voucher ini belum pernah digunakan checkout oleh pelanggan mana pun.</p>
                    </div>

                    <div x-show="!loadingUsages && currentUsages.length > 0" class="overflow-x-auto max-h-[400px] rounded-lg border border-slate-200">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold sticky top-0 border-b border-slate-200">
                                <tr>
                                    <th class="px-3.5 py-2.5">No. Order</th>
                                    <th class="px-3.5 py-2.5">Pelanggan</th>
                                    <th class="px-3.5 py-2.5">Perangkat (Device)</th>
                                    <th class="px-3.5 py-2.5">Device Fingerprint</th>
                                    <th class="px-3.5 py-2.5">IP & Waktu</th>
                                    <th class="px-3.5 py-2.5 text-right">Potongan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="item in currentUsages" :key="item.id">
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-3.5 py-2.5 font-mono font-bold text-slate-900" x-text="item.order_number"></td>
                                        <td class="px-3.5 py-2.5">
                                            <p class="font-bold text-slate-900" x-text="item.user_name"></p>
                                            <p class="text-[10px] text-slate-400" x-text="item.user_email"></p>
                                        </td>
                                        <td class="px-3.5 py-2.5">
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-medium text-[11px] border border-slate-200">
                                                <i class="fa-solid fa-laptop text-[10px] text-orange-600"></i>
                                                <span x-text="item.device_label"></span>
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-2.5 font-mono text-[10px] text-slate-400 truncate max-w-[120px]" :title="item.device_id" x-text="item.device_id ? item.device_id.substring(0, 16) + '...' : '—'"></td>
                                        <td class="px-3.5 py-2.5">
                                            <p class="font-mono text-slate-700 font-medium" x-text="item.ip_address"></p>
                                            <p class="text-[10px] text-slate-400" x-text="item.created_at_human"></p>
                                        </td>
                                        <td class="px-3.5 py-2.5 text-right font-extrabold text-emerald-600" x-text="item.formatted_amount"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 flex justify-end">
                    <button type="button" @click="showUsagesModal = false" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 7. FLOATING BULK ACTION BAR ── --}}
    <div x-cloak
         x-show="selectedVouchers.length > 0"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-3"
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-slate-900 text-white px-5 py-3 rounded-xl shadow-xl border border-slate-700 flex items-center gap-4">
        <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-md bg-orange-500 text-white font-extrabold text-xs flex items-center justify-center" x-text="selectedVouchers.length"></span>
            <span class="text-xs font-medium text-slate-300">Voucher dipilih</span>
        </div>

        <div class="h-4 w-px bg-slate-700"></div>

        <form action="{{ route('admin.vouchers.destroy') }}" method="POST"
              @submit="return confirm('Yakin ingin menghapus ' + selectedVouchers.length + ' voucher terpilih?')">
            @csrf
            @method('DELETE')
            <template x-for="id in selectedVouchers" :key="id">
                <input type="hidden" name="voucher_ids[]" :value="id">
            </template>
            <button type="submit"
                    class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold py-1.5 px-3.5 rounded-lg transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-trash-can text-xs"></i>
                Hapus Terpilih
            </button>
        </form>

        <button type="button" @click="selectedVouchers = []"
                class="text-xs font-bold text-slate-400 hover:text-white transition cursor-pointer">
            Batal
        </button>
    </div>
</div>

<script>
function voucherManager() {
    return {
        formTab: 'promo',
        selectedVouchers: [],
        allVoucherIds: {{ Js::from($vouchers->pluck('id')->values()->all()) }},
        rawAmount: '{{ old('amount', 50000) }}',
        displayAmount: '',
        rawPromoAmount: '{{ old('amount', 50000) }}',
        displayPromoAmount: '',
        showUsagesModal: false,
        loadingUsages: false,
        currentVoucher: { code: '', quota_label: '' },
        currentUsages: [],
        init() {
            this.formatAmount(this.rawAmount);
            this.formatPromoAmount(this.rawPromoAmount);
        },
        formatAmount(val) {
            let clean = String(val || '').replace(/\D/g, '');
            this.rawAmount = clean;
            this.displayAmount = clean !== '' ? Number(clean).toLocaleString('id-ID') : '';
            if (this.$refs.displayAmountInput) {
                this.$refs.displayAmountInput.value = this.displayAmount;
            }
        },
        formatPromoAmount(val) {
            let clean = String(val || '').replace(/\D/g, '');
            this.rawPromoAmount = clean;
            this.displayPromoAmount = clean !== '' ? Number(clean).toLocaleString('id-ID') : '';
            if (this.$refs.displayPromoAmountInput) {
                this.$refs.displayPromoAmountInput.value = this.displayPromoAmount;
            }
        },
        get isAllSelected() {
            return this.allVoucherIds.length > 0 && this.selectedVouchers.length === this.allVoucherIds.length;
        },
        toggleSelectAll() {
            if (this.isAllSelected) {
                this.selectedVouchers = [];
            } else {
                this.selectedVouchers = [...this.allVoucherIds];
            }
        },
        async openUsagesModal(voucherId) {
            this.showUsagesModal = true;
            this.loadingUsages = true;
            this.currentVoucher = { code: '...', quota_label: '...' };
            this.currentUsages = [];
            try {
                const res = await fetch('/admin/vouchers/' + voucherId + '/usages');
                const data = await res.json();
                if (data.success) {
                    this.currentVoucher = data.voucher;
                    this.currentUsages = data.usages;
                }
            } catch (e) {
                alert('Gagal memuat log pemakaian voucher.');
            } finally {
                this.loadingUsages = false;
            }
        }
    };
}
</script>
@endsection
