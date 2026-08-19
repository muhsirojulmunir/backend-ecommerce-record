@extends('layouts.app')

@section('title', 'Kelola Ulasan')
@section('page_title', 'Kelola Ulasan')
@section('page_subtitle', 'Ulasan tayang seketika. Tugasmu di sini menyapu yang kasar, spam, atau salah alamat.')

@section('content')
<div class="space-y-6">

    {{-- ── Kartu Statistik ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @php
            // Kelas warna ditulis utuh, bukan dirangkai dari potongan seperti
            // "bg-{$warna}-50". Tailwind memindai kode saat build dan hanya
            // membuat kelas yang tertulis lengkap.
            $kartu = [
                ['Rata-rata Bintang', number_format($rataRata, 2), 'fa-star',       'bg-amber-50 text-amber-600'],
                ['Tampil di Toko',    number_format($stats['tampil']), 'fa-eye',    'bg-emerald-50 text-emerald-600'],
                ['Disembunyikan',     number_format($stats['disembunyikan']), 'fa-eye-slash', 'bg-slate-100 text-slate-500'],
                ['Bintang 1-2',       number_format($stats['rendah']), 'fa-triangle-exclamation', 'bg-rose-50 text-rose-600'],
            ];
        @endphp

        @foreach($kartu as [$judul, $nilai, $ikon, $kelasWarna])
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl {{ $kelasWarna }} flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid {{ $ikon }}"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-400">{{ $judul }}</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ $nilai }}</h3>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Penyaring ── --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('admin.reviews') }}"
              class="flex flex-col lg:flex-row lg:items-end gap-4">

            <div class="flex-1">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Cari</label>
                <input type="text" name="cari" value="{{ $cari }}"
                       placeholder="Isi ulasan, nama produk, nama pembeli, atau nomor pesanan"
                       class="w-full rounded-xl border-gray-200 text-sm focus:border-orange-500 focus:ring-orange-500">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Status</label>
                <select name="status" class="rounded-xl border-gray-200 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="semua" @selected($status === 'semua')>Semua ({{ $stats['semua'] }})</option>
                    <option value="tampil" @selected($status === 'tampil')>Tampil ({{ $stats['tampil'] }})</option>
                    <option value="disembunyikan" @selected($status === 'disembunyikan')>Disembunyikan ({{ $stats['disembunyikan'] }})</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Bintang</label>
                <select name="bintang" class="rounded-xl border-gray-200 text-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="0" @selected($bintang === 0)>Semua bintang</option>
                    @for($b = 5; $b >= 1; $b--)
                        <option value="{{ $b }}" @selected($bintang === $b)>{{ $b }} bintang</option>
                    @endfor
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-sm font-bold transition">
                    <i class="fa-solid fa-filter mr-1"></i> Saring
                </button>
                @if($cari !== '' || $status !== 'semua' || $bintang !== 0)
                    <a href="{{ route('admin.reviews') }}"
                       class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-bold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── Daftar Ulasan ── --}}
    @forelse($daftar as $u)
        <div class="bg-white rounded-2xl p-5 shadow-sm border {{ $u->is_hidden ? 'border-slate-200 bg-slate-50/60' : 'border-gray-100' }}">

            <div class="flex flex-col lg:flex-row lg:items-start gap-5">

                {{-- Produk yang diulas --}}
                <a href="{{ route('admin.products.edit', $u->product_id) }}"
                   class="flex items-center gap-3 lg:w-64 shrink-0 group">
                    @if($u->product?->image)
                        <img src="{{ Storage::url($u->product->image) }}" alt="{{ $u->product->name }}"
                             class="w-12 h-12 rounded-xl object-cover border border-gray-200 shrink-0">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 shrink-0">
                            <i class="fa-solid fa-box"></i>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-gray-800 line-clamp-2 group-hover:text-orange-600 transition">
                            {{ $u->product?->name ?? 'Produk terhapus' }}
                        </p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $u->order?->order_number }}</p>
                    </div>
                </a>

                {{-- Isi ulasan --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-sm tracking-wide">
                            @for($b = 1; $b <= 5; $b++)
                                <i class="fa-solid fa-star {{ $b <= $u->rating ? 'text-amber-400' : 'text-gray-200' }}"></i>
                            @endfor
                        </span>

                        {{-- Nama lengkap sengaja ditampilkan DI SINI, berbeda
                             dari halaman toko yang menyamarkannya. Admin perlu
                             tahu persis siapa yang menulis untuk bisa
                             menindaklanjuti keluhannya. --}}
                        <span class="text-xs font-bold text-gray-700">{{ $u->user?->name ?? 'Pembeli' }}</span>
                        <span class="text-[11px] text-gray-400">{{ $u->created_at->translatedFormat('d M Y, H:i') }}</span>

                        @if($u->is_hidden)
                            <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-bold uppercase tracking-wider">
                                <i class="fa-solid fa-eye-slash mr-1"></i> Disembunyikan
                            </span>
                        @endif
                    </div>

                    @if($u->comment)
                        <p class="mt-2 text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $u->comment }}</p>
                    @else
                        <p class="mt-2 text-xs text-gray-400 italic">Tanpa komentar — pembeli hanya memberi bintang.</p>
                    @endif

                    @if(count($u->daftar_foto))
                        <div class="flex gap-2 mt-3 flex-wrap">
                            @foreach($u->daftar_foto as $foto)
                                <a href="{{ Storage::url($foto) }}" target="_blank" rel="noopener"
                                   class="block w-16 h-16 rounded-xl overflow-hidden border border-gray-200 hover:border-orange-400 transition">
                                    <img src="{{ Storage::url($foto) }}" alt="Foto ulasan" class="w-full h-full object-cover">
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if($u->is_hidden && $u->hidden_reason)
                        <div class="mt-3 rounded-xl bg-slate-100 px-3 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Alasan disembunyikan</p>
                            <p class="text-xs text-slate-600 mt-0.5">{{ $u->hidden_reason }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">
                                oleh {{ $u->disembunyikanOleh?->name ?? 'admin' }}
                                @if($u->hidden_at) • {{ $u->hidden_at->translatedFormat('d M Y, H:i') }} @endif
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Tindakan --}}
                <div class="flex lg:flex-col gap-2 shrink-0" x-data="{ tanya: false }">
                    @if($u->is_hidden)
                        <form method="POST" action="{{ route('admin.reviews.toggle', $u->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="w-full px-4 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold transition whitespace-nowrap">
                                <i class="fa-solid fa-eye mr-1"></i> Tampilkan Lagi
                            </button>
                        </form>
                    @else
                        <button type="button" @click="tanya = !tanya"
                                class="w-full px-4 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-bold transition whitespace-nowrap">
                            <i class="fa-solid fa-eye-slash mr-1"></i> Sembunyikan
                        </button>
                    @endif

                    <form method="POST" action="{{ route('admin.reviews.destroy', $u->id) }}"
                          onsubmit="return confirm('Hapus ulasan ini permanen beserta fotonya? Yang terhapus tidak bisa dikembalikan — kalau ragu, sembunyikan saja.');">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition whitespace-nowrap">
                            <i class="fa-solid fa-trash mr-1"></i> Hapus
                        </button>
                    </form>

                    {{-- Alasan diminta di tempat, bukan di halaman terpisah:
                         keputusannya kecil dan tidak perlu berpindah halaman. --}}
                    <div x-show="tanya" x-cloak
                         class="lg:absolute lg:right-5 lg:mt-24 bg-white border border-amber-200 rounded-xl p-3 shadow-lg z-10 w-64">
                        <form method="POST" action="{{ route('admin.reviews.toggle', $u->id) }}">
                            @csrf @method('PATCH')
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">
                                Alasan disembunyikan
                            </label>
                            <input type="text" name="alasan" required maxlength="255"
                                   placeholder="mis. mengandung kata kasar"
                                   class="w-full rounded-lg border-gray-200 text-xs focus:border-amber-500 focus:ring-amber-500">
                            <div class="flex gap-2 mt-2">
                                <button type="submit"
                                        class="flex-1 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition">
                                    Sembunyikan
                                </button>
                                <button type="button" @click="tanya = false"
                                        class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold transition">
                                    Batal
                                </button>
                            </div>
                            @error('alasan') <p class="text-[10px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl p-12 shadow-sm border border-gray-100 text-center">
            <i class="fa-regular fa-star text-4xl text-gray-200"></i>
            <p class="mt-3 text-sm font-bold text-gray-600">
                @if($cari !== '' || $status !== 'semua' || $bintang !== 0)
                    Tidak ada ulasan yang cocok dengan saringanmu
                @else
                    Belum ada ulasan sama sekali
                @endif
            </p>
            <p class="mt-1 text-xs text-gray-400">
                @if($cari !== '' || $status !== 'semua' || $bintang !== 0)
                    Coba longgarkan saringannya.
                @else
                    Ulasan muncul di sini setelah pembeli menilai pesanan yang sudah selesai.
                @endif
            </p>
        </div>
    @endforelse

    @if($daftar->hasPages())
        <div>{{ $daftar->links() }}</div>
    @endif
</div>
@endsection
