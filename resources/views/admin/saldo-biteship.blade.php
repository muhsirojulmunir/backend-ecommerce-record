@extends('layouts.app')

@section('title', 'Saldo Biteship')
@section('page_title', 'Saldo Biteship')
@section('page_subtitle', 'Pantau sisa saldo agar tidak ada pesanan yang gagal dikirim')

@section('content')
@php
    $rupiah = fn ($n) => 'Rp ' . number_format((int) $n, 0, ',', '.');
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Keadaan saat ini ── --}}
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-black text-gray-800 uppercase tracking-wide mb-5">Perkiraan Sisa Saldo</h3>

            @if($ringkasan['perkiraan'] === null)
                <div class="py-8 text-center">
                    <i class="fa-solid fa-wallet text-3xl text-gray-200"></i>
                    <p class="mt-3 text-sm font-bold text-gray-500">Saldo belum pernah dicatat</p>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto leading-relaxed">
                        Buka dasbor Biteship, lihat angka saldonya, lalu tuliskan di formulir
                        sebelah. Setelah itu sistem bisa memperkirakan sisanya sendiri.
                    </p>
                </div>
            @else
                @php
                    $warna = match ($ringkasan['nada']) {
                        'bahaya' => 'text-rose-600',
                        'awas'   => 'text-amber-600',
                        default  => 'text-emerald-600',
                    };
                @endphp

                <p class="text-4xl font-black {{ $warna }}">{{ $rupiah($ringkasan['perkiraan']) }}</p>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Terakhir dicatat</p>
                        <p class="font-bold text-gray-700 mt-1">{{ $rupiah($riwayat->first()->saldo_tercatat ?? 0) }}</p>
                        <p class="text-[10px] text-gray-400">{{ $ringkasan['dicatat']?->translatedFormat('d M Y, H:i') }}</p>
                    </div>

                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Ongkir terpakai</p>
                        <p class="font-bold text-gray-700 mt-1">− {{ $rupiah($terpakai) }}</p>
                        <p class="text-[10px] text-gray-400">sejak dicatat</p>
                    </div>

                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Batas peringatan</p>
                        <p class="font-bold text-gray-700 mt-1">{{ $rupiah($ambang) }}</p>
                        <p class="text-[10px] text-gray-400">diatur di config</p>
                    </div>
                </div>
            @endif

            {{-- Batas ketelitian dinyatakan terang-terangan. Angka perkiraan yang
                 dikira pasti justru lebih berbahaya daripada tidak ada angka. --}}
            <div class="mt-5 p-3.5 rounded-xl bg-sky-50 border border-sky-100">
                <p class="text-[11px] text-sky-800 leading-relaxed">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    <strong>Ini perkiraan, bukan angka pasti.</strong>
                    Biteship tidak menyediakan cara membaca saldo lewat API, jadi sistem
                    menghitungnya dari catatanmu dikurangi ongkir tiap pengiriman. Biaya kecil
                    per panggilan — Tracking Rp 10, Rates Rp 5, Maps Rp 2 — belum ikut terhitung,
                    sehingga saldo sebenarnya selalu sedikit lebih rendah. Cocokkan ulang dengan
                    dasbor Biteship sesekali.
                </p>
            </div>
        </div>

        {{-- ── Riwayat pencatatan ── --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-black text-gray-800 uppercase tracking-wide mb-4">Riwayat Pencatatan</h3>

            @forelse($riwayat as $baris)
                <div class="flex items-center gap-3 py-3 {{ ! $loop->last ? 'border-b border-gray-50' : '' }}">
                    <span class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-arrow-up text-xs"></i>
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-gray-800">{{ $rupiah($baris->saldo_tercatat) }}</p>
                        <p class="text-[10px] text-gray-400">
                            {{ $baris->dicatat_pada->translatedFormat('d M Y, H:i') }}
                            @if($baris->pencatat) &middot; {{ $baris->pencatat->name }} @endif
                            @if($baris->catatan) &middot; {{ $baris->catatan }} @endif
                        </p>
                    </div>
                </div>
            @empty
                <p class="py-8 text-center text-xs text-gray-400">Belum ada catatan.</p>
            @endforelse
        </div>
    </div>

    {{-- ── Formulir pencatatan ── --}}
    <div class="lg:col-span-1">
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6 lg:sticky lg:top-6">
            <h3 class="text-sm font-black text-gray-800 uppercase tracking-wide mb-1.5">Catat Saldo</h3>
            <p class="text-[11px] text-gray-400 leading-relaxed mb-5">
                Lakukan setiap selesai mengisi ulang. Perhitungan sisa dimulai ulang dari angka ini.
            </p>

            <form method="POST" action="{{ route('admin.saldo-biteship.simpan') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">
                        Saldo di dasbor Biteship
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-bold">Rp</span>
                        <input type="number" name="saldo_tercatat" required min="0" step="1"
                               value="{{ old('saldo_tercatat') }}"
                               class="w-full pl-9 pr-3 py-2.5 text-sm rounded-xl border border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                               placeholder="250000">
                    </div>
                    @error('saldo_tercatat')
                        <p class="text-[10px] text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-[10px] text-gray-400 mt-1.5">Angka saja, tanpa titik.</p>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">
                        Catatan <span class="font-normal normal-case">(opsional)</span>
                    </label>
                    <input type="text" name="catatan" maxlength="255" value="{{ old('catatan') }}"
                           class="w-full px-3 py-2.5 text-xs rounded-xl border border-gray-200 focus:border-orange-500 focus:ring-orange-500"
                           placeholder="mis. isi ulang lewat BCA">
                </div>

                <button type="submit"
                        class="w-full bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold py-3 rounded-xl transition shadow-sm">
                    Simpan Catatan
                </button>
            </form>

            <a href="https://biteship.com/dashboard" target="_blank" rel="noopener noreferrer"
               class="mt-3 block text-center text-[11px] font-bold text-gray-400 hover:text-orange-600 transition">
                Buka dasbor Biteship <i class="fa-solid fa-arrow-up-right-from-square text-[9px] ml-0.5"></i>
            </a>
        </div>
    </div>
</div>
@endsection
