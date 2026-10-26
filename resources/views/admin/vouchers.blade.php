@extends('layouts.app')

@section('title', 'Kelola Voucher')
@section('page_title', 'Kelola Voucher')

@section('content')
<div class="space-y-6" x-data="voucherManager()">

    {{-- Flash --}}
    @if(session('success'))
        <div class="flash-alert flex items-center justify-between bg-green-50 border border-green-200 text-green-800 text-sm px-5 py-3.5 rounded-2xl shadow-sm transition-all duration-500 overflow-hidden">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-green-500"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-green-600 hover:text-green-800 transition p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="flash-alert flex items-center justify-between bg-red-50 border border-red-200 text-red-800 text-sm px-5 py-3.5 rounded-2xl shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-xmark text-red-500"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-red-600 hover:text-red-800 transition p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    {{-- ── Statistik Cards ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-ticket text-white text-sm"></i>
                </div>
                <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Total</p>
            </div>
            <p class="text-2xl font-extrabold text-gray-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-fuchsia-500 to-pink-600 flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-bullhorn text-white text-sm"></i>
                </div>
                <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Promo Massal</p>
            </div>
            <p class="text-2xl font-extrabold text-fuchsia-600">{{ number_format($stats['promo'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-check-circle text-white text-sm"></i>
                </div>
                <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Tersedia</p>
            </div>
            <p class="text-2xl font-extrabold text-emerald-600">{{ number_format($stats['available']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-gamepad text-white text-sm"></i>
                </div>
                <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Klaim Game</p>
            </div>
            <p class="text-2xl font-extrabold text-indigo-600">{{ number_format($stats['claimed'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-receipt text-white text-sm"></i>
                </div>
                <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Terpakai</p>
            </div>
            <p class="text-2xl font-extrabold text-amber-600">{{ number_format($stats['used']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 col-span-2 lg:col-span-1">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-rose-500 flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-clock text-white text-sm"></i>
                </div>
                <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Kadaluarsa</p>
            </div>
            <p class="text-2xl font-extrabold text-red-500">{{ number_format($stats['expired']) }}</p>
        </div>
    </div>

    {{-- ── Form Pembuatan Voucher (Tabs) ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-white font-black text-sm flex items-center gap-2">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-400"></i>
                        Buat Voucher Baru
                    </h3>
                    <p class="text-slate-300 text-xs mt-0.5">Pilih tipe voucher yang ingin dibuat untuk toko Anda.</p>
                </div>

                {{-- Tabs Pemilihan Tipe Form --}}
                <div class="inline-flex p-1 bg-white/10 backdrop-blur-md rounded-xl border border-white/15">
                    <button type="button" @click="formTab = 'promo'"
                        :class="formTab === 'promo' ? 'bg-white text-slate-900 shadow-md' : 'text-slate-300 hover:text-white'"
                        class="px-4 py-1.5 rounded-lg text-xs font-black transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-shield-halved text-fuchsia-600"></i>
                        Voucher Promo (RECORD)
                    </button>
                    <button type="button" @click="formTab = 'batch'"
                        :class="formTab === 'batch' ? 'bg-white text-slate-900 shadow-md' : 'text-slate-300 hover:text-white'"
                        class="px-4 py-1.5 rounded-lg text-xs font-black transition flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-ticket text-indigo-500"></i>
                        Generate Kupon Batch Acak
                    </button>
                </div>
            </div>
        </div>

        {{-- ── FORM 1: Voucher Promo Massal (RECORD) ── --}}
        <form x-show="formTab === 'promo'" action="{{ route('admin.vouchers.promo.store') }}" method="POST" class="p-6">
            @csrf

            {{-- Info Alert Proteksi --}}
            <div class="mb-5 bg-fuchsia-50/70 border border-fuchsia-200 text-fuchsia-950 text-xs p-4 rounded-xl flex items-start gap-3">
                <i class="fa-solid fa-shield-halved text-fuchsia-600 text-base mt-0.5"></i>
                <div class="space-y-1">
                    <p class="font-bold">Proteksi Anti-Kecurangan Aktif Otomatis:</p>
                    <p class="text-fuchsia-800 leading-relaxed">
                        Voucher Promo Massal (seperti <strong>RECORD</strong>) dapat dipakai bersama oleh banyak pelanggan, namun sistem otomatis mengunci batas maksimal <strong>1x penggunaan per Akun</strong> dan <strong>1x penggunaan per Perangkat fisik (Device Fingerprint)</strong>.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5">
                {{-- Kode Voucher --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                        <i class="fa-solid fa-font text-fuchsia-600 mr-1"></i>Kode Voucher
                    </label>
                    <input type="text" name="code" value="{{ old('code', 'RECORD') }}" required maxlength="20"
                        placeholder="Contoh: RECORD"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-mono font-bold uppercase tracking-wider focus:ring-2 focus:ring-fuchsia-500 focus:outline-none focus:border-transparent transition @error('code') border-red-400 @enderror">
                    @error('code')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nominal Diskon --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                        <i class="fa-solid fa-money-bill text-emerald-500 mr-1"></i>Nominal Potongan (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-xs font-extrabold text-gray-400">Rp</span>
                        <input type="text"
                               x-ref="displayPromoAmountInput"
                               :value="displayPromoAmount"
                               @input="formatPromoAmount($event.target.value)"
                               value="{{ old('amount') ? number_format((int)old('amount'), 0, ',', '.') : '50.000' }}"
                               placeholder="50.000"
                               required
                               autocomplete="off"
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-fuchsia-500 focus:outline-none focus:border-transparent transition @error('amount') border-red-400 @enderror">
                    </div>
                    <input type="hidden" name="amount" :value="rawPromoAmount" value="{{ old('amount', 50000) }}">
                    @error('amount')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kuota Pemakaian --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                        <i class="fa-solid fa-users text-indigo-500 mr-1"></i>Batas Kuota Total
                        <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <input type="number" name="total_quota" value="{{ old('total_quota') }}" min="1" placeholder="Kosongkan jika unlimited"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-fuchsia-500 focus:outline-none focus:border-transparent transition">
                    <p class="text-[10px] text-gray-400 mt-1">Contoh: 100 orang pertama</p>
                </div>

                {{-- Kadaluarsa --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                        <i class="fa-solid fa-calendar-xmark text-amber-500 mr-1"></i>Masa Berlaku
                        <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-fuchsia-500 focus:outline-none focus:border-transparent transition @error('expires_at') border-red-400 @enderror">
                    @error('expires_at')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Target Kategori --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                        <i class="fa-solid fa-layer-group text-blue-500 mr-1"></i>Kategori Produk
                    </label>
                    <select name="category_id" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-fuchsia-500 focus:outline-none focus:border-transparent transition">
                        <option value="">Semua Kategori (Universal)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-5 flex items-center gap-3">
                <button type="submit"
                    class="bg-gradient-to-r from-fuchsia-600 to-purple-600 hover:from-fuchsia-700 hover:to-purple-700 text-white text-sm font-bold py-2.5 px-6 rounded-xl transition shadow-md shadow-fuchsia-200 flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-shield-halved"></i>
                    Simpan Voucher Promo
                </button>
            </div>
        </form>

        {{-- ── FORM 2: Generate Kupon Batch Acak ── --}}
        <form x-show="formTab === 'batch'" action="{{ route('admin.vouchers.generate') }}" method="POST" class="p-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5">
                {{-- Jumlah --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1.5">
                        <i class="fa-solid fa-hashtag text-indigo-500 mr-1"></i>Jumlah Voucher
                    </label>
                    <input type="number" name="quantity" value="{{ old('quantity', 10) }}" min="1" max="500" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none focus:border-transparent transition @error('quantity') border-red-400 @enderror">
                    @error('quantity')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nominal --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1.5">
                        <i class="fa-solid fa-money-bill text-emerald-500 mr-1"></i>Nominal (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-xs font-extrabold text-gray-400">Rp</span>
                        <input type="text"
                               x-ref="displayAmountInput"
                               :value="displayAmount"
                               @input="formatAmount($event.target.value)"
                               value="{{ old('amount') ? number_format((int)old('amount'), 0, ',', '.') : '50.000' }}"
                               placeholder="10.000"
                               required
                               autocomplete="off"
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none focus:border-transparent transition @error('amount') border-red-400 @enderror">
                    </div>
                    <input type="hidden" name="amount" id="voucher_raw_amount" :value="rawAmount" value="{{ old('amount', 50000) }}">
                    @error('amount')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kadaluarsa --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1.5">
                        <i class="fa-solid fa-calendar-xmark text-amber-500 mr-1"></i>Kadaluarsa
                        <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none focus:border-transparent transition @error('expires_at') border-red-400 @enderror">
                    @error('expires_at')
                        <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Label Batch --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1.5">
                        <i class="fa-solid fa-tag text-purple-500 mr-1"></i>Label Batch
                        <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <input type="text" name="batch_label" value="{{ old('batch_label') }}" placeholder="Auto-generate jika kosong" maxlength="100"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none focus:border-transparent transition placeholder-gray-300">
                </div>

                {{-- Target Kategori --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1.5">
                        <i class="fa-solid fa-layer-group text-blue-500 mr-1"></i>Target Kategori
                    </label>
                    <select name="category_id" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none focus:border-transparent transition">
                        <option value="">Semua Kategori (Universal)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-5 flex items-center gap-3">
                <button type="submit"
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-sm font-bold py-2.5 px-6 rounded-xl transition shadow-md shadow-indigo-200 flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    Generate Kupon Batch
                </button>
                <p class="text-[10px] text-gray-400">
                    <i class="fa-solid fa-circle-info mr-0.5"></i>
                    Maksimal 500 voucher per batch. Kode acak 4 karakter sekali pakai (untuk print atau reward game).
                </p>
            </div>
        </form>
    </div>

    {{-- ── Filter & Pencarian ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <form action="{{ route('admin.vouchers') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Cari Kode</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode..."
                        class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition uppercase">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tipe</label>
                <select name="type" class="px-4 py-2.5 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    <option value="">Semua Tipe</option>
                    <option value="promo" {{ request('type') === 'promo' ? 'selected' : '' }}>Promo Massal (RECORD)</option>
                    <option value="single" {{ request('type') === 'single' ? 'selected' : '' }}>Kupon Acak (Single-Use)</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="px-4 py-2.5 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    <option value="">Semua Status</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia / Aktif</option>
                    <option value="claimed" {{ request('status') === 'claimed' ? 'selected' : '' }}>Diklaim User (Game)</option>
                    <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>Terpakai (Checkout)</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Kadaluarsa</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Kategori</label>
                <select name="category_id" class="px-4 py-2.5 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition max-w-[180px]">
                    <option value="">Semua Kategori</option>
                    <option value="all" {{ request('category_id') === 'all' ? 'selected' : '' }}>Universal (Semua)</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Batch</label>
                <select name="batch" class="px-4 py-2.5 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition max-w-[200px]">
                    <option value="">Semua Batch</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch }}" {{ request('batch') === $batch ? 'selected' : '' }}>{{ $batch }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-bold py-2.5 px-5 rounded-xl transition shadow-sm cursor-pointer">
                    <i class="fa-solid fa-filter mr-1"></i>Filter
                </button>
                <a href="{{ route('admin.vouchers') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold py-2.5 px-5 rounded-xl transition">
                    <i class="fa-solid fa-rotate mr-1"></i>Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ── Aksi Batch ── --}}
    @if(request('batch'))
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.vouchers.print', ['batch' => request('batch')]) }}" target="_blank"
                class="bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-white text-xs font-bold py-2.5 px-5 rounded-xl transition shadow-md shadow-teal-200 flex items-center gap-2">
                <i class="fa-solid fa-print"></i>
                Cetak Batch Ini (F4)
            </a>

            <form action="{{ route('admin.vouchers.destroy') }}" method="POST"
                onsubmit="return confirm('Yakin hapus semua voucher BELUM TERPAKAI di batch ini?')">
                @csrf
                @method('DELETE')
                <input type="hidden" name="batch_label" value="{{ request('batch') }}">
                <button type="submit"
                    class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold py-2.5 px-5 rounded-xl transition border border-red-200 flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-trash"></i>
                    Hapus Batch (Belum Terpakai)
                </button>
            </form>
        </div>
    @endif

    {{-- ── Tabel Voucher ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="w-12 px-4 py-3.5 text-center">
                            <input type="checkbox"
                                   @click="toggleSelectAll()"
                                   :checked="isAllSelected"
                                   class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 cursor-pointer"
                                   title="Centang Semua Voucher di Halaman Ini">
                        </th>
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Kode</th>
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Nominal</th>
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Batch / Label</th>
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Status / Kuota</th>
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Kadaluarsa</th>
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Dipakai Oleh / Log</th>
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Dibuat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($vouchers as $v)
                        <tr class="hover:bg-gray-50/50 transition" :class="{ 'bg-indigo-50/40': selectedVouchers.includes({{ $v->id }}) }">
                            {{-- Checkbox --}}
                            <td class="w-12 px-4 py-3.5 text-center" @click.stop>
                                <input type="checkbox"
                                       :value="{{ $v->id }}"
                                       x-model.number="selectedVouchers"
                                       class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 cursor-pointer">
                            </td>

                            {{-- Kode --}}
                            <td class="px-5 py-3.5">
                                <div class="flex flex-col gap-1.5 items-start">
                                    <span class="inline-flex items-center gap-1.5 bg-gray-900 text-white font-mono font-extrabold text-sm px-3 py-1.5 rounded-lg tracking-[0.2em] select-all shadow-sm">
                                        {{ $v->code }}
                                    </span>
                                    @if($v->isPromo())
                                        <span class="inline-flex items-center gap-1 bg-fuchsia-50 text-fuchsia-700 border border-fuchsia-200 font-extrabold text-[9px] px-2 py-0.5 rounded-md uppercase tracking-wider">
                                            <i class="fa-solid fa-shield-halved text-[8px]"></i> Promo Massal
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Nominal --}}
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-emerald-600 text-sm">{{ $v->formatted_amount }}</span>
                            </td>

                            {{-- Kategori --}}
                            <td class="px-5 py-3.5">
                                @if($v->category)
                                    <span class="inline-flex items-center gap-1.5 bg-blue-50 border border-blue-200 text-blue-700 font-bold text-xs px-2.5 py-1 rounded-lg">
                                        <i class="fa-solid fa-layer-group text-[10px]"></i>
                                        {{ $v->category->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 bg-gray-100 border border-gray-200 text-gray-600 font-medium text-xs px-2.5 py-1 rounded-lg">
                                        <i class="fa-solid fa-globe text-[10px]"></i>
                                        Semua Kategori
                                    </span>
                                @endif
                            </td>

                            {{-- Batch --}}
                            <td class="px-5 py-3.5">
                                @if($v->batch_label)
                                    <a href="{{ route('admin.vouchers', ['batch' => $v->batch_label]) }}"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold hover:underline transition">
                                        {{ \Illuminate\Support\Str::limit($v->batch_label, 20) }}
                                    </a>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Status / Kuota --}}
                            <td class="px-5 py-3.5">
                                @if($v->isPromo())
                                    @if($v->is_expired)
                                        <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 text-[10px] font-bold px-2.5 py-1 rounded-lg border border-red-200">
                                            <i class="fa-solid fa-clock text-[9px]"></i> Kadaluarsa
                                        </span>
                                    @elseif($v->total_quota !== null && $v->used_count >= $v->total_quota)
                                        <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 text-[10px] font-bold px-2.5 py-1 rounded-lg border border-amber-200">
                                            <i class="fa-solid fa-ban text-[9px]"></i> Kuota Habis
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2.5 py-1 rounded-lg border border-emerald-200">
                                            <i class="fa-solid fa-circle-check text-[9px]"></i> Promo Aktif
                                        </span>
                                    @endif
                                    <p class="text-[10px] text-gray-500 font-medium mt-1">
                                        Kuota: <strong class="text-gray-800">{{ $v->used_count }} / {{ $v->total_quota ? number_format($v->total_quota) : '∞' }}</strong>
                                    </p>
                                @elseif($v->is_used)
                                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2.5 py-1 rounded-lg border border-emerald-200">
                                        <i class="fa-solid fa-circle-check text-[9px]"></i> Terpakai
                                    </span>
                                @elseif($v->used_by && $v->usedByUser)
                                    <span class="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 text-[10px] font-bold px-2.5 py-1 rounded-lg border border-indigo-200" title="Disimpan oleh user dari game">
                                        <i class="fa-solid fa-gamepad text-[9px]"></i> Diklaim (Game)
                                    </span>
                                @elseif($v->reserved_for && $v->reserved_until && $v->reserved_until->isFuture())
                                    <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 text-[10px] font-bold px-2.5 py-1 rounded-lg border border-amber-200" title="Sedang dimainkan user">
                                        <i class="fa-solid fa-stopwatch text-[9px]"></i> Sesi Game
                                    </span>
                                @elseif($v->is_expired)
                                    <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 text-[10px] font-bold px-2.5 py-1 rounded-lg border border-red-200">
                                        <i class="fa-solid fa-clock text-[9px]"></i> Kadaluarsa
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 bg-teal-50 text-teal-700 text-[10px] font-bold px-2.5 py-1 rounded-lg border border-teal-200">
                                        <i class="fa-solid fa-circle text-[8px]"></i> Tersedia
                                    </span>
                                @endif
                            </td>

                            {{-- Kadaluarsa --}}
                            <td class="px-5 py-3.5 text-xs text-gray-500">
                                {{ $v->expires_at ? $v->expires_at->format('d M Y') : '—' }}
                            </td>

                            {{-- Dipakai Oleh / Riwayat Log --}}
                            <td class="px-5 py-3.5">
                                @if($v->isPromo())
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="openUsagesModal({{ $v->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                                            <i class="fa-solid fa-clock-rotate-left text-xs text-indigo-600"></i>
                                            <span>Riwayat ({{ $v->used_count }})</span>
                                        </button>
                                    </div>
                                @elseif($v->is_used && $v->usedByUser)
                                    <div class="text-xs">
                                        <p class="font-bold text-gray-800 flex items-center gap-1">
                                            <i class="fa-solid fa-user-check text-emerald-500 text-[10px]"></i>
                                            {{ $v->usedByUser->name }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">
                                            @if($v->order)
                                                Pesanan #{{ $v->order->order_number ?? $v->order->id }} • 
                                            @endif
                                            {{ $v->used_at?->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                @elseif($v->used_by && $v->usedByUser)
                                    <div class="text-xs">
                                        <p class="font-bold text-indigo-700 flex items-center gap-1">
                                            <i class="fa-solid fa-gift text-indigo-500 text-[10px]"></i>
                                            {{ $v->usedByUser->name }}
                                        </p>
                                        <p class="text-[10px] text-indigo-500 mt-0.5 font-medium">Tersimpan di akun (belum checkout)</p>
                                    </div>
                                @elseif($v->reserved_for && $v->reservedByUser && $v->reserved_until && $v->reserved_until->isFuture())
                                    <div class="text-xs">
                                        <p class="font-bold text-amber-700 flex items-center gap-1">
                                            <i class="fa-solid fa-stopwatch text-amber-500 text-[10px]"></i>
                                            {{ $v->reservedByUser->name }}
                                        </p>
                                        <p class="text-[10px] text-amber-500 mt-0.5">Reservasi aktif ({{ $v->reserved_until->diffForHumans() }})</p>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Dibuat --}}
                            <td class="px-5 py-3.5 text-xs text-gray-500">
                                {{ $v->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-300">
                                    <i class="fa-solid fa-ticket text-4xl"></i>
                                    <p class="text-sm font-semibold">Belum ada voucher</p>
                                    <p class="text-xs">Buat voucher pertama Anda pada form di atas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginasi --}}
        @if($vouchers->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>

    {{-- ── Modal Riwayat Pemakaian Voucher Promo ── --}}
    <div x-cloak x-show="showUsagesModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showUsagesModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                 @click="showUsagesModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showUsagesModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-100">
                
                {{-- Modal Header --}}
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 px-6 py-4 flex items-center justify-between text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                            <i class="fa-solid fa-shield-halved text-fuchsia-400"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-base flex items-center gap-2">
                                Riwayat Pemakaian: <span class="text-amber-400 font-mono tracking-wider font-extrabold" x-text="currentVoucher.code"></span>
                            </h3>
                            <p class="text-xs text-slate-300 mt-0.5">
                                Aturan: 1x Perangkat & 1x Akun • Terpakai: <span class="font-bold text-white" x-text="currentVoucher.quota_label"></span>
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showUsagesModal = false" class="text-slate-400 hover:text-white transition p-1.5 rounded-lg hover:bg-white/10 cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6">
                    <div x-show="loadingUsages" class="py-12 text-center text-gray-400">
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl text-indigo-500 mb-3"></i>
                        <p class="text-xs font-semibold">Memuat log pemakaian perangkat...</p>
                    </div>

                    <div x-show="!loadingUsages && currentUsages.length === 0" class="py-12 text-center text-gray-400">
                        <i class="fa-solid fa-clipboard-list text-4xl text-gray-200 mb-3"></i>
                        <p class="text-sm font-bold text-gray-600">Belum Ada Pemakaian</p>
                        <p class="text-xs text-gray-400 mt-1">Voucher ini belum pernah digunakan checkout oleh pelanggan mana pun.</p>
                    </div>

                    <div x-show="!loadingUsages && currentUsages.length > 0" class="overflow-x-auto max-h-[420px] rounded-xl border border-gray-100">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50/90 text-gray-500 uppercase text-[10px] font-extrabold sticky top-0 border-b border-gray-100">
                                <tr>
                                    <th class="px-4 py-3">No. Order</th>
                                    <th class="px-4 py-3">Pelanggan</th>
                                    <th class="px-4 py-3">Perangkat (Device)</th>
                                    <th class="px-4 py-3">Device Fingerprint</th>
                                    <th class="px-4 py-3">IP & Waktu</th>
                                    <th class="px-4 py-3 text-right">Potongan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="item in currentUsages" :key="item.id">
                                    <tr class="hover:bg-indigo-50/30 transition">
                                        <td class="px-4 py-3 font-mono font-bold text-indigo-600" x-text="item.order_number"></td>
                                        <td class="px-4 py-3">
                                            <p class="font-bold text-gray-800" x-text="item.user_name"></p>
                                            <p class="text-[10px] text-gray-400" x-text="item.user_email"></p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium text-[11px] border border-slate-200">
                                                <i class="fa-solid fa-laptop text-[10px] text-indigo-500"></i>
                                                <span x-text="item.device_label"></span>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-mono text-[10px] text-gray-400 truncate max-w-[120px]" :title="item.device_id" x-text="item.device_id ? item.device_id.substring(0, 16) + '...' : '—'"></td>
                                        <td class="px-4 py-3">
                                            <p class="font-mono text-gray-600 font-semibold" x-text="item.ip_address"></p>
                                            <p class="text-[10px] text-gray-400" x-text="item.created_at_human"></p>
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-emerald-600" x-text="item.formatted_amount"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="bg-gray-50 px-6 py-3.5 flex justify-end">
                    <button type="button" @click="showUsagesModal = false" class="px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded-xl transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Floating Bulk Action Bar ── --}}
    <div x-cloak
         x-show="selectedVouchers.length > 0"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-slate-900 text-white px-6 py-3.5 rounded-2xl shadow-2xl border border-slate-700/80 flex items-center gap-5">
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-full bg-indigo-500 text-white font-extrabold text-xs flex items-center justify-center" x-text="selectedVouchers.length"></span>
            <span class="text-xs font-semibold text-slate-200">Voucher dipilih</span>
        </div>

        <div class="h-5 w-px bg-slate-700"></div>

        <form action="{{ route('admin.vouchers.destroy') }}" method="POST"
              @submit="return confirm('Yakin ingin menghapus ' + selectedVouchers.length + ' voucher terpilih secara permanen?')">
            @csrf
            @method('DELETE')
            <template x-for="id in selectedVouchers" :key="id">
                <input type="hidden" name="voucher_ids[]" :value="id">
            </template>
            <button type="submit"
                    class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-2 px-4 rounded-xl transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-trash-can text-xs"></i>
                Hapus Voucher Terpilih
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
