@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Ringkasan Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Sales Card -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center space-x-5">
            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-rupiah-sign"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-400">Total Penjualan</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</h3>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center space-x-5">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-shopping-bag"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-400">Total Pesanan</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($summary['total_orders']) }}</h3>
            </div>
        </div>

        <!-- Customers Card -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center space-x-5">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-400">Total Pelanggan</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($summary['total_customers']) }}</h3>
            </div>
        </div>

        <!-- Products Card -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center space-x-5">
            <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-400">Jumlah Produk</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($summary['total_products']) }}</h3>
            </div>
        </div>
    </div>

    <!-- Status Breakdown Grid -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h4 class="text-base font-bold text-gray-800 mb-4">Status Pesanan Aktif</h4>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-100 text-center">
                <p class="text-xs text-gray-400 font-semibold uppercase">Pending / Belum Bayar</p>
                <p class="text-2xl font-bold text-gray-700 mt-2">{{ $summary['status_counts']['pending'] }}</p>
            </div>
            <div class="p-4 rounded-xl bg-yellow-50 border border-yellow-100 text-center">
                <p class="text-xs text-yellow-600 font-semibold uppercase">Perlu Dikirim</p>
                <p class="text-2xl font-bold text-yellow-700 mt-2">{{ $summary['status_counts']['processing'] }}</p>
            </div>
            <div class="p-4 rounded-xl bg-blue-50 border border-blue-100 text-center">
                <p class="text-xs text-blue-600 font-semibold uppercase">Sedang Dikirim</p>
                <p class="text-2xl font-bold text-blue-700 mt-2">{{ $summary['status_counts']['shipped'] }}</p>
            </div>
            <div class="p-4 rounded-xl bg-green-50 border border-green-100 text-center">
                <p class="text-xs text-green-600 font-semibold uppercase">Selesai</p>
                <p class="text-2xl font-bold text-green-700 mt-2">{{ $summary['status_counts']['completed'] }}</p>
            </div>
            <div class="p-4 rounded-xl bg-red-50 border border-red-100 col-span-2 md:col-span-1 text-center">
                <p class="text-xs text-red-600 font-semibold uppercase">Batal & Retur</p>
                <p class="text-2xl font-bold text-red-700 mt-2">{{ $summary['status_counts']['cancelled'] + $summary['status_counts']['returned'] }}</p>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Orders (2/3 width on wide screens) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 lg:col-span-2">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-base font-bold text-gray-800">Pesanan Terbaru</h4>
                <a href="#" class="text-sm font-semibold text-orange-500 hover:text-orange-600 transition">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500">
                    <thead class="text-xs text-gray-400 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">No. Pesanan</th>
                            <th class="px-4 py-3">Pelanggan</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($summary['recent_orders'] as $order)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-4 py-4 font-semibold text-gray-900">{{ $order->order_number }}</td>
                                <td class="px-4 py-4">{{ $order->user->name ?? 'Guest' }}</td>
                                <td class="px-4 py-4 font-medium text-gray-800">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                                <td class="px-4 py-4">
                                    @if($order->status === 'pending')
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Belum Bayar</span>
                                    @elseif($order->status === 'processing')
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Perlu Dikirim</span>
                                    @elseif($order->status === 'shipped')
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Dikirim</span>
                                    |@elseif($order->status === 'completed')
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $order->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada pesanan terbaru.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity Logs (1/3 width) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h4 class="text-base font-bold text-gray-800 mb-6">Aktivitas Sistem</h4>
            <div class="flow-root">
                <ul class="-mb-8">
                    @forelse($summary['recent_activities'] as $act)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 text-xs">
                                            <i class="fa-solid fa-info"></i>
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0 pt-1.5">
                                        <p class="text-xs text-gray-600">{{ $act->description }}</p>
                                        <div class="text-right text-[10px] whitespace-nowrap text-gray-400 mt-1">
                                            <time datetime="{{ $act->created_at }}">{{ $act->created_at->diffForHumans() }}</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">Belum ada catatan aktivitas.</p>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
