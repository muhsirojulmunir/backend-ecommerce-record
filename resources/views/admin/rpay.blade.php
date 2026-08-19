@extends('layouts.app')

@section('title', 'Kelola R_Pay')
@section('page_title', 'Kelola R_Pay')
@section('page_subtitle', 'Saldo dompet digital seluruh pembeli beserta mutasinya.')

@section('content')
<div class="space-y-6">

    {{-- ── Ringkasan ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @php
            $kartu = [
                ['Akun Bersaldo',  number_format($stats['akun_bersaldo']),                 'fa-users',        'bg-purple-50 text-purple-600', null],
                ['Total Saldo',    'Rp ' . number_format($stats['total_saldo'], 0, ',', '.'), 'fa-wallet',       'bg-blue-50 text-blue-600',     'Kewajiban toko ke pembeli'],
                ['Saldo Masuk',    'Rp ' . number_format($stats['masuk'], 0, ',', '.'),      'fa-arrow-down',   'bg-emerald-50 text-emerald-600', 'Sejak awal'],
                ['Saldo Terpakai', 'Rp ' . number_format($stats['keluar'], 0, ',', '.'),     'fa-arrow-up',     'bg-amber-50 text-amber-600',   'Belanja + pencairan'],
            ];
        @endphp

        @foreach($kartu as [$judul, $nilai, $ikon, $kelasWarna, $ket])
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl {{ $kelasWarna }} flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid {{ $ikon }}"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-400">{{ $judul }}</p>
                <h3 class="text-lg font-black text-gray-800 mt-0.5 truncate">{{ $nilai }}</h3>
                @if($ket)<p class="text-[10px] text-gray-400 font-semibold">{{ $ket }}</p>@endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Penyaring & Ekspor ── --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex gap-1.5">
                @foreach(['bersaldo' => 'Punya Saldo', 'pernah' => 'Pernah Bertransaksi', 'semua' => 'Semua Akun'] as $kode => $label)
                    <a href="{{ route('admin.rpay', array_filter(['hanya' => $kode, 'cari' => $cari])) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $hanya === $kode ? 'bg-orange-600 text-white' : 'bg-gray-50 text-gray-500 hover:bg-gray-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="flex-1 min-w-[200px] flex gap-2">
                <input type="hidden" name="hanya" value="{{ $hanya }}">
                <input type="text" name="cari" value="{{ $cari }}" placeholder="Cari nama atau email…"
                       class="flex-1 text-xs border border-gray-200 rounded-xl px-3 py-2.5 focus:ring-orange-500 focus:border-orange-500">
                <button class="bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold px-4 rounded-xl transition">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>

            <a href="{{ route('admin.rpay.export') }}"
               class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-file-excel"></i> Ekspor Excel
            </a>
        </form>
    </div>

    {{-- ── Daftar Akun ── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="text-left font-bold px-5 py-3.5">Akun</th>
                        <th class="text-left font-bold px-5 py-3.5">Peran</th>
                        <th class="text-right font-bold px-5 py-3.5">Saldo R_Pay</th>
                        <th class="text-center font-bold px-5 py-3.5">Mutasi</th>
                        <th class="px-5 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($akun as $orang)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="px-5 py-4">
                            <p class="font-bold text-gray-800">{{ $orang->name }}</p>
                            <p class="text-[10px] text-gray-400">{{ $orang->email }}</p>
                        </td>
                        <td class="px-5 py-4 text-gray-500">{{ $orang->role }}</td>
                        <td class="px-5 py-4 text-right">
                            <span class="font-black {{ $orang->rpay_balance > 0 ? 'text-emerald-700' : 'text-gray-400' }}">
                                Rp {{ number_format($orang->rpay_balance, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center text-gray-500 font-semibold">{{ $orang->jumlah_mutasi }}</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.rpay.show', $orang->id) }}"
                               class="inline-flex items-center gap-1.5 bg-gray-800 hover:bg-gray-900 text-white text-[11px] font-bold px-3.5 py-2 rounded-xl transition">
                                Rincian <i class="fa-solid fa-arrow-right text-[9px]"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <i class="fa-solid fa-wallet text-3xl text-gray-200"></i>
                            <p class="text-gray-400 font-semibold mt-3">Belum ada akun dengan saldo R_Pay.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($akun->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">{{ $akun->links() }}</div>
        @endif
    </div>
</div>
@endsection
