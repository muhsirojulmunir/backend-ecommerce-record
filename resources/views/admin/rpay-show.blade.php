@extends('layouts.app')

@section('title', 'Mutasi R_Pay')
@section('page_title', 'Mutasi R_Pay')
@section('page_subtitle', $pemilik->name . ' — ' . $pemilik->email)

@section('content')
@php
    // Kolom cache dibandingkan dengan hasil penjumlahan buku besar. Kalau
    // keduanya berbeda, itu tanda ada yang menulis saldo di luar RpayService
    // dan harus diketahui sedini mungkin.
    $selisih = round((float) $pemilik->rpay_balance - $saldoBukuBesar, 2);
@endphp

<div class="space-y-6">
    <a href="{{ route('admin.rpay') }}" class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 hover:text-gray-800">
        <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke daftar akun
    </a>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-gray-400">Saldo Menurut Buku Besar</p>
            <h3 class="text-2xl font-black text-gray-800 mt-1">Rp {{ number_format($saldoBukuBesar, 0, ',', '.') }}</h3>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <p class="text-xs font-semibold text-gray-400">Saldo Tersimpan di Akun</p>
            <h3 class="text-2xl font-black text-gray-800 mt-1">Rp {{ number_format($pemilik->rpay_balance, 0, ',', '.') }}</h3>
        </div>

        <div class="rounded-2xl p-5 shadow-sm border {{ $selisih === 0.0 ? 'bg-emerald-50 border-emerald-100' : 'bg-rose-50 border-rose-200' }}">
            <p class="text-xs font-semibold {{ $selisih === 0.0 ? 'text-emerald-700' : 'text-rose-700' }}">Kecocokan</p>
            <h3 class="text-lg font-black mt-1 {{ $selisih === 0.0 ? 'text-emerald-800' : 'text-rose-800' }}">
                {{ $selisih === 0.0 ? 'Cocok' : 'Selisih Rp ' . number_format(abs($selisih), 0, ',', '.') }}
            </h3>
            @if($selisih !== 0.0)
            <p class="text-[10px] text-rose-600 font-semibold mt-0.5">Ada saldo yang ditulis di luar sistem — perlu diperiksa.</p>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="text-left font-bold px-5 py-3.5">Waktu</th>
                        <th class="text-left font-bold px-5 py-3.5">Sumber</th>
                        <th class="text-left font-bold px-5 py-3.5">Keterangan</th>
                        <th class="text-right font-bold px-5 py-3.5">Nominal</th>
                        <th class="text-right font-bold px-5 py-3.5">Saldo Sesudah</th>
                        <th class="text-left font-bold px-5 py-3.5">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($mutasi as $baris)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="px-5 py-4 text-gray-500 whitespace-nowrap">{{ $baris->created_at->format('d M Y H:i') }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold bg-gray-100 text-gray-600">
                                {{ $baris->sumber_label }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-600 max-w-[280px]">{{ $baris->description }}</td>
                        <td class="px-5 py-4 text-right font-black whitespace-nowrap {{ $baris->direction === 'credit' ? 'text-emerald-700' : 'text-rose-600' }}">
                            {{ $baris->direction === 'credit' ? '+' : '−' }} Rp {{ number_format($baris->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-right font-bold text-gray-700 whitespace-nowrap">
                            Rp {{ number_format($baris->balance_after, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-gray-400">{{ $baris->dibuatOleh?->name ?? 'sistem' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <i class="fa-solid fa-receipt text-3xl text-gray-200"></i>
                            <p class="text-gray-400 font-semibold mt-3">Akun ini belum pernah bertransaksi R_Pay.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mutasi->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">{{ $mutasi->links() }}</div>
        @endif
    </div>
</div>
@endsection
