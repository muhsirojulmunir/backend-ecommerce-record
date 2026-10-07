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
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
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

    {{-- ── Generate Voucher Form ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
            <h3 class="text-white font-bold text-sm flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                Generate Voucher Baru
            </h3>
            <p class="text-indigo-200 text-xs mt-1">Buat kode voucher secara massal dengan kode acak 4 karakter.</p>
        </div>

        <form action="{{ route('admin.vouchers.generate') }}" method="POST" class="p-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
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
                    <input type="number" name="amount" value="{{ old('amount', 50000) }}" min="1000" step="1000" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none focus:border-transparent transition @error('amount') border-red-400 @enderror">
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
            </div>

            <div class="mt-5 flex items-center gap-3">
                <button type="submit"
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-sm font-bold py-2.5 px-6 rounded-xl transition shadow-md shadow-indigo-200 flex items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    Generate Voucher
                </button>
                <p class="text-[10px] text-gray-400">
                    <i class="fa-solid fa-circle-info mr-0.5"></i>
                    Maksimal 500 voucher per batch. Kode terdiri dari 4 karakter acak (huruf & angka).
                </p>
            </div>
        </form>
    </div>

    {{-- ── Filter & Search ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <form method="GET" action="{{ route('admin.vouchers') }}" class="flex flex-col lg:flex-row gap-3 items-start lg:items-end">
            <div class="flex-1 max-w-xs relative">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Cari Kode</label>
                <span class="absolute bottom-0 left-0 pl-3 flex items-center h-[42px] text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Ketik kode voucher..."
                    class="block w-full pl-8 pr-3 py-2.5 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition placeholder-gray-400">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="px-4 py-2.5 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    <option value="">Semua Status</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia</option>
                    <option value="claimed" {{ request('status') === 'claimed' ? 'selected' : '' }}>Diklaim User (Game)</option>
                    <option value="used" {{ request('status') === 'used' ? 'selected' : '' }}>Terpakai (Checkout)</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Kadaluarsa</option>
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
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-bold py-2.5 px-5 rounded-xl transition shadow-sm">
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
                    class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold py-2.5 px-5 rounded-xl transition border border-red-200 flex items-center gap-2">
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
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Kode</th>
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Nominal</th>
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Batch</th>
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Kadaluarsa</th>
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Dipakai Oleh</th>
                        <th class="px-5 py-3.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Dibuat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($vouchers as $v)
                        <tr class="hover:bg-gray-50/50 transition">
                            {{-- Kode --}}
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 bg-gray-900 text-white font-mono font-extrabold text-sm px-3 py-1.5 rounded-lg tracking-[0.3em] select-all">
                                    {{ $v->code }}
                                </span>
                            </td>

                            {{-- Nominal --}}
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-emerald-600 text-sm">{{ $v->formatted_amount }}</span>
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

                            {{-- Status --}}
                            <td class="px-5 py-3.5">
                                @if($v->is_used)
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

                            {{-- Dipakai Oleh / Pemilik --}}
                            <td class="px-5 py-3.5">
                                @if($v->is_used && $v->usedByUser)
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
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-300">
                                    <i class="fa-solid fa-ticket text-4xl"></i>
                                    <p class="text-sm font-semibold">Belum ada voucher</p>
                                    <p class="text-xs">Generate voucher pertama Anda di form di atas.</p>
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
</div>

<script>
function voucherManager() {
    return {
        // placeholder untuk fitur interaktif di masa depan
    };
}
</script>
@endsection
