@extends('layouts.app')

@section('title', 'Detail Customer')
@section('page_title', 'Detail Customer')
@section('page_subtitle', $customer->name)

@section('content')
<div class="space-y-6">

    {{-- ── Tombol Kembali ── --}}
    <a href="{{ route('admin.customers') }}"
       class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 hover:text-gray-800 transition">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke Daftar Customer
    </a>

    {{-- ── Kartu Profil ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-6 py-8 flex flex-col sm:flex-row items-start sm:items-center gap-5">
            @if($customer->avatar_url)
                <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}"
                     class="w-20 h-20 rounded-2xl object-cover border-2 border-white/20 shrink-0">
            @else
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-orange-500 to-rose-500 flex items-center justify-center font-black text-white text-3xl shrink-0 shadow-lg">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="text-xl font-black text-white">{{ $customer->name }}</h2>
                    @if($customer->is_blocked)
                        <span class="px-2.5 py-1 text-[10px] font-black rounded-full bg-rose-500 text-white uppercase">Diblokir</span>
                    @else
                        <span class="px-2.5 py-1 text-[10px] font-black rounded-full bg-emerald-500 text-white uppercase">Aktif</span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-x-5 gap-y-1 mt-2 text-xs text-slate-300">
                    <span><i class="fa-solid fa-envelope mr-1.5 text-slate-500"></i>{{ $customer->email }}</span>
                    <span><i class="fa-solid fa-phone mr-1.5 text-slate-500"></i>{{ $customer->phone ?: 'Belum diisi' }}</span>
                    <span><i class="fa-solid fa-calendar-check mr-1.5 text-slate-500"></i>Bergabung {{ $customer->created_at->translatedFormat('d F Y') }}</span>
                </div>
            </div>

            <form action="{{ route('admin.customers.toggle-block', $customer->id) }}" method="POST" class="shrink-0"
                  onsubmit="return confirm('{{ $customer->is_blocked ? 'Buka blokir akun ini?' : 'Blokir akun ini? Customer tidak akan bisa login.' }}')">
                @csrf @method('PATCH')
                <button type="submit"
                    class="text-xs font-bold px-5 py-2.5 rounded-xl transition shadow flex items-center gap-2
                        {{ $customer->is_blocked ? 'bg-emerald-500 hover:bg-emerald-600 text-white' : 'bg-white/10 hover:bg-rose-500 text-white border border-white/20' }}">
                    <i class="fa-solid {{ $customer->is_blocked ? 'fa-lock-open' : 'fa-ban' }}"></i>
                    {{ $customer->is_blocked ? 'Buka Blokir' : 'Blokir Akun' }}
                </button>
            </form>
        </div>

        {{-- Ringkasan angka --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-gray-100">
            <div class="p-5">
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-wide">Total Pesanan</p>
                <p class="text-2xl font-black text-gray-800 mt-1">{{ number_format($summary['total_orders']) }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">{{ $summary['paid_orders'] }} sudah lunas</p>
            </div>
            <div class="p-5">
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-wide">Total Belanja</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($summary['total_spent'], 0, ',', '.') }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">dari pesanan lunas</p>
            </div>
            <div class="p-5">
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-wide">Rata-rata / Pesanan</p>
                <p class="text-2xl font-black text-gray-800 mt-1">Rp {{ number_format($summary['avg_order'], 0, ',', '.') }}</p>
            </div>
            <div class="p-5">
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-wide">Belanja Terakhir</p>
                <p class="text-base font-black text-gray-800 mt-1.5">
                    {{ $summary['last_order_at'] ? \Carbon\Carbon::parse($summary['last_order_at'])->translatedFormat('d M Y') : '—' }}
                </p>
                @if($summary['last_order_at'])
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($summary['last_order_at'])->diffForHumans() }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Riwayat Pesanan ── --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h4 class="text-sm font-black text-gray-800 uppercase tracking-wide">Riwayat Pesanan</h4>
                <span class="text-[10px] font-bold text-gray-400">{{ $orders->total() }} pesanan</span>
            </div>

            @if($orders->isEmpty())
                <div class="p-12 text-center">
                    <i class="fa-regular fa-file-lines text-3xl text-gray-300 mb-3 block"></i>
                    <p class="text-sm font-bold text-gray-500">Customer ini belum pernah berbelanja</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-500">
                        <thead class="text-xs text-gray-400 uppercase bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 font-bold">No. Pesanan</th>
                                <th class="px-5 py-3 font-bold">Tanggal</th>
                                <th class="px-5 py-3 font-bold text-center">Item</th>
                                <th class="px-5 py-3 font-bold">Total</th>
                                <th class="px-5 py-3 font-bold">Status</th>
                                <th class="px-5 py-3 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($orders as $order)
                                @php
                                    $badge = match($order->status) {
                                        'pending'    => 'bg-gray-100 text-gray-700',
                                        'processing' => 'bg-amber-100 text-amber-800',
                                        'shipped'    => 'bg-blue-100 text-blue-800',
                                        'completed'  => 'bg-emerald-100 text-emerald-800',
                                        default      => 'bg-rose-100 text-rose-800',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-5 py-4">
                                        <p class="font-bold text-gray-800 text-xs">{{ $order->order_number }}</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $order->payment_method }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-xs">
                                        {{ $order->created_at->translatedFormat('d M Y') }}
                                        <p class="text-[10px] text-gray-400">{{ $order->created_at->format('H:i') }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-center text-xs font-bold text-gray-700">{{ $order->items->sum('quantity') }}</td>
                                    <td class="px-5 py-4 font-bold text-gray-800 text-xs">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                                    <td class="px-5 py-4">
                                        <span class="px-2.5 py-1 text-[10px] font-black rounded-full uppercase {{ $badge }}">{{ $order->status_label }}</span>
                                        @if($order->payment_status !== 'paid')
                                            <p class="text-[9px] text-rose-500 font-bold mt-1">{{ $order->payment_status_label }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('admin.orders.show', $order->id) }}"
                                           class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                            Lihat
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

        {{-- ── Sidebar: Alamat, Produk Favorit, Sebaran Status ── --}}
        <div class="space-y-6">

            {{-- Alamat tersimpan --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h4 class="text-sm font-black text-gray-800 uppercase tracking-wide mb-4">Alamat Tersimpan</h4>

                @forelse($customer->addresses as $address)
                    <div class="p-4 rounded-xl border mb-3 last:mb-0 {{ $address->is_default ? 'border-orange-200 bg-orange-50/50' : 'border-gray-100 bg-gray-50/50' }}">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-black text-gray-800">{{ $address->label }}</span>
                            @if($address->is_default)
                                <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-orange-500 text-white">Utama</span>
                            @endif
                        </div>
                        <p class="text-xs font-bold text-gray-700">{{ $address->recipient_name }}</p>
                        <p class="text-[11px] text-gray-500">{{ $address->phone }}</p>
                        <p class="text-[11px] text-gray-500 mt-1.5 leading-relaxed">
                            {{ $address->address_line }},<br>
                            {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}
                        </p>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 text-center py-4">Belum ada alamat tersimpan.</p>
                @endforelse
            </div>

            {{-- Produk favorit --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h4 class="text-sm font-black text-gray-800 uppercase tracking-wide mb-4">Paling Sering Dibeli</h4>

                @forelse($favoriteProducts as $fav)
                    <div class="flex items-center justify-between gap-3 py-2.5 border-b border-gray-50 last:border-0">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-gray-700 truncate">{{ $fav->product_name }}</p>
                            <p class="text-[10px] text-gray-400">Rp {{ number_format($fav->revenue, 0, ',', '.') }}</p>
                        </div>
                        <span class="shrink-0 px-2.5 py-1 rounded-lg bg-orange-50 text-orange-700 text-xs font-black">{{ $fav->qty }}x</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 text-center py-4">Belum ada data pembelian.</p>
                @endforelse
            </div>

            {{-- Sebaran status pesanan --}}
            @if($summary['total_orders'] > 0)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h4 class="text-sm font-black text-gray-800 uppercase tracking-wide mb-4">Sebaran Status</h4>
                    @php
                        $statusMeta = [
                            'pending'    => ['Menunggu',   'bg-gray-400'],
                            'processing' => ['Diproses',   'bg-amber-500'],
                            'shipped'    => ['Dikirim',    'bg-blue-500'],
                            'completed'  => ['Selesai',    'bg-emerald-500'],
                            'cancelled'  => ['Dibatalkan', 'bg-rose-500'],
                        ];
                    @endphp
                    @foreach($statusMeta as $key => [$label, $color])
                        @php $count = (int) ($summary['status_counts'][$key] ?? 0); @endphp
                        <div class="mb-3 last:mb-0">
                            <div class="flex justify-between text-[11px] font-bold mb-1">
                                <span class="text-gray-600">{{ $label }}</span>
                                <span class="text-gray-800">{{ $count }}</span>
                            </div>
                            <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full {{ $color }} rounded-full transition-all duration-500"
                                     style="width: {{ $summary['total_orders'] > 0 ? ($count / $summary['total_orders'] * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
