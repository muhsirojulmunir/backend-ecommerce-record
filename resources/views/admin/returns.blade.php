@extends('layouts.app')

@section('title', 'Kelola Pengembalian')
@section('page_title', 'Kelola Pengembalian')
@section('page_subtitle', 'Tinjau pengajuan pengembalian barang dari pembeli, lalu setujui atau tolak.')

@section('content')
<div class="space-y-6">

    {{-- ── Kartu Statistik ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @php
            // Kelas warna ditulis utuh, bukan dirangkai dari potongan seperti
            // "bg-{$warna}-50". Tailwind memindai kode saat build dan hanya
            // membuat kelas yang tertulis lengkap — kelas rangkaian tidak akan
            // pernah ada di CSS jadinya, sehingga warnanya hilang diam-diam.
            $kartu = [
                ['Menunggu Ditinjau', $stats['pending'],  'fa-hourglass-half', 'bg-amber-50 text-amber-600'],
                ['Disetujui',         $stats['approved'], 'fa-circle-check',   'bg-emerald-50 text-emerald-600'],
                ['Ditolak',           $stats['rejected'], 'fa-circle-xmark',   'bg-rose-50 text-rose-600'],
            ];
        @endphp

        @foreach($kartu as [$judul, $nilai, $ikon, $kelasWarna])
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl {{ $kelasWarna }} flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid {{ $ikon }}"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-400">{{ $judul }}</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ number_format($nilai) }}</h3>
            </div>
        </div>
        @endforeach

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-400">Dana Dikembalikan</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">Rp {{ number_format($stats['nilai'], 0, ',', '.') }}</h3>
                <p class="text-[10px] text-gray-400 font-semibold">Masuk ke R_Pay pembeli</p>
            </div>
        </div>
    </div>

    {{-- ── Penyaring ── --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex gap-1.5 flex-wrap">
                @foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'semua' => 'Semua'] as $kode => $label)
                    <a href="{{ route('admin.returns', array_filter(['status' => $kode, 'cari' => $cari])) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $status === $kode ? 'bg-orange-600 text-white' : 'bg-gray-50 text-gray-500 hover:bg-gray-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="flex-1 min-w-[220px] flex gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="text" name="cari" value="{{ $cari }}" placeholder="Cari nomor pesanan, nama, atau email…"
                       class="flex-1 text-xs border border-gray-200 rounded-xl px-3 py-2.5 focus:ring-orange-500 focus:border-orange-500">
                <button class="bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold px-4 rounded-xl transition">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </form>
    </div>

    {{-- ── Daftar Pengajuan ── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="text-left font-bold px-5 py-3.5">Pesanan</th>
                        <th class="text-left font-bold px-5 py-3.5">Pembeli</th>
                        <th class="text-left font-bold px-5 py-3.5">Alasan</th>
                        <th class="text-left font-bold px-5 py-3.5">Diminta</th>
                        <th class="text-left font-bold px-5 py-3.5">Diajukan</th>
                        <th class="text-left font-bold px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($daftar as $item)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="px-5 py-4">
                            <p class="font-bold text-gray-800">{{ $item->return_number ?? '—' }}</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">
                                Pesanan {{ $item->order?->order_number ?? '—' }}
                            </p>
                            <p class="text-[10px] text-gray-400 font-semibold">
                                Rp {{ number_format($item->order?->grand_total ?? 0, 0, ',', '.') }}
                            </p>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-gray-700">{{ $item->user?->name ?? '—' }}</p>
                            <p class="text-[10px] text-gray-400">{{ $item->user?->email }}</p>
                        </td>
                        <td class="px-5 py-4 max-w-[220px]">
                            <p class="font-semibold text-gray-700">{{ $item->reason_label }}</p>
                            <p class="text-[10px] text-gray-400 truncate">
                                {{ $item->reason ?: 'tanpa penjelasan tambahan' }}
                            </p>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold
                                {{ $item->resolution === 'refund' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                {{ $item->resolution === 'refund' ? 'Dana' : 'Tukar Barang' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-500">{{ $item->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-4">
                            @php
                                $kelasStatus = [
                                    'pending'  => 'bg-amber-50 text-amber-700',
                                    'approved' => 'bg-emerald-50 text-emerald-700',
                                    'rejected' => 'bg-rose-50 text-rose-700',
                                ][$item->status] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $kelasStatus }}">
                                {{ $item->status_label }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.returns.show', $item->id) }}"
                               class="inline-flex items-center gap-1.5 bg-gray-800 hover:bg-gray-900 text-white text-[11px] font-bold px-3.5 py-2 rounded-xl transition">
                                Tinjau <i class="fa-solid fa-arrow-right text-[9px]"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <i class="fa-solid fa-inbox text-3xl text-gray-200"></i>
                            <p class="text-gray-400 font-semibold mt-3">Belum ada pengajuan pengembalian.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($daftar->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">{{ $daftar->links() }}</div>
        @endif
    </div>
</div>
@endsection
