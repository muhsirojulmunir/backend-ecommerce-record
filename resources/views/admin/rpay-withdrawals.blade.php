@extends('layouts.app')

@section('title', 'Pencairan R_Pay')
@section('page_title', 'Pencairan R_Pay ke Bank')
@section('page_subtitle', 'Proses permintaan pencairan saldo R_Pay ke rekening bank pembeli.')

@section('content')
<div class="space-y-6" x-data="{ buka: null, keputusan: '' }">

    {{-- ── Ringkasan ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @php
            $kartu = [
                ['Menunggu Diproses', number_format($stats['pending']),                            'fa-hourglass-half',      'bg-amber-50 text-amber-600'],
                ['Sedang Diproses',   number_format($stats['processing']),                         'fa-spinner',             'bg-blue-50 text-blue-600'],
                ['Nilai Dalam Antrean', 'Rp ' . number_format($stats['nilai_antre'], 0, ',', '.'), 'fa-money-bill-transfer', 'bg-purple-50 text-purple-600'],
                ['Sudah Dicairkan',   'Rp ' . number_format($stats['nilai_cair'], 0, ',', '.'),    'fa-circle-check',        'bg-emerald-50 text-emerald-600'],
            ];
        @endphp

        @foreach($kartu as [$judul, $nilai, $ikon, $kelasWarna])
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl {{ $kelasWarna }} flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid {{ $ikon }}"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-400">{{ $judul }}</p>
                <h3 class="text-lg font-black text-gray-800 mt-0.5 truncate">{{ $nilai }}</h3>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Penyaring ── --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex gap-1.5 flex-wrap">
        @foreach(['pending' => 'Menunggu', 'processing' => 'Diproses', 'completed' => 'Selesai', 'rejected' => 'Ditolak', 'semua' => 'Semua'] as $kode => $label)
            <a href="{{ route('admin.rpay.withdrawals', ['status' => $kode]) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $status === $kode ? 'bg-orange-600 text-white' : 'bg-gray-50 text-gray-500 hover:bg-gray-100' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- ── Daftar Pencairan ── --}}
    <div class="space-y-4">
        @forelse($daftar as $cair)
        @php
            $kelasStatus = [
                'pending'    => 'bg-amber-50 text-amber-700 border-amber-200',
                'processing' => 'bg-blue-50 text-blue-700 border-blue-200',
                'completed'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'rejected'   => 'bg-rose-50 text-rose-700 border-rose-200',
            ][$cair->status] ?? 'bg-gray-50 text-gray-600 border-gray-200';
        @endphp

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <p class="font-black text-gray-800">{{ $cair->reference }}</p>
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold border {{ $kelasStatus }}">
                            {{ $cair->status_label }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1.5">
                        {{ $cair->user?->name }} &middot; {{ $cair->user?->email }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Diajukan {{ $cair->created_at->format('d M Y, H:i') }}
                        @if($cair->estimated_ready_at)
                            &middot; perkiraan cair {{ $cair->estimated_ready_at->format('d M Y') }}
                        @endif
                    </p>
                </div>

                <div class="text-right shrink-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Nominal</p>
                    <p class="text-xl font-black text-gray-800">Rp {{ number_format($cair->amount, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-50 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Bank</p>
                    <p class="font-bold text-gray-700">{{ $cair->bank_name }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Nomor Rekening</p>
                    <p class="font-bold text-gray-700 font-mono">{{ $cair->account_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Atas Nama</p>
                    <p class="font-bold text-gray-700">{{ $cair->account_holder }}</p>
                </div>
            </div>

            @if($cair->admin_notes)
            <div class="mt-3 bg-gray-50 rounded-xl p-3 text-xs text-gray-600">
                <span class="font-bold">Catatan:</span> {{ $cair->admin_notes }}
            </div>
            @endif

            @if(in_array($cair->status, ['pending', 'processing'], true))
            <form method="POST" action="{{ route('admin.rpay.withdrawals.process', $cair->id) }}"
                  class="mt-4 pt-4 border-t border-gray-50 space-y-3">
                @csrf
                <input type="hidden" name="keputusan" x-ref="keputusan{{ $cair->id }}">

                <textarea name="admin_notes" rows="2"
                          placeholder="Catatan untuk pembeli (wajib bila ditolak)"
                          class="w-full text-xs border border-gray-200 rounded-xl px-3 py-2.5 focus:ring-orange-500 focus:border-orange-500"></textarea>

                <div class="flex flex-wrap gap-2">
                    @if($cair->status === 'pending')
                    <button type="submit" @click="$refs.keputusan{{ $cair->id }}.value = 'processing'"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition">
                        <i class="fa-solid fa-spinner mr-1.5"></i> Tandai Diproses
                    </button>
                    @endif

                    <button type="submit" @click="$refs.keputusan{{ $cair->id }}.value = 'completed'"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition">
                        <i class="fa-solid fa-circle-check mr-1.5"></i> Dana Sudah Dikirim
                    </button>

                    <button type="submit" @click="$refs.keputusan{{ $cair->id }}.value = 'rejected'"
                            class="bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold px-4 py-2.5 rounded-xl transition">
                        <i class="fa-solid fa-circle-xmark mr-1.5"></i> Tolak &amp; Kembalikan Saldo
                    </button>
                </div>

                <p class="text-[10px] text-gray-400">
                    Saldo pembeli sudah dipotong sejak pengajuan dibuat. Menolak akan mengembalikannya ke R_Pay.
                </p>
            </form>
            @elseif($cair->processed_at)
            <p class="mt-4 pt-4 border-t border-gray-50 text-[11px] text-gray-400">
                Diproses oleh <strong>{{ $cair->diprosesOleh?->name ?? 'sistem' }}</strong>
                pada {{ $cair->processed_at->format('d M Y, H:i') }}.
            </p>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-5 py-16 text-center">
            <i class="fa-solid fa-money-bill-transfer text-3xl text-gray-200"></i>
            <p class="text-gray-400 font-semibold mt-3">Tidak ada permintaan pencairan pada penyaring ini.</p>
        </div>
        @endforelse
    </div>

    @if($daftar->hasPages())
        <div>{{ $daftar->links() }}</div>
    @endif
</div>
@endsection
