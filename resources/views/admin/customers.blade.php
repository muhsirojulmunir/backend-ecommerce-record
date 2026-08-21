@extends('layouts.app')

@section('title', 'Kelola Customer')
@section('page_title', 'Kelola Customer')
@section('page_subtitle', 'Pantau pelanggan, riwayat belanja, dan status akun mereka.')

@section('content')
<div class="space-y-6" x-data="{ showDetail: false, detail: {} }">

    {{-- ── Kartu Statistik ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-400">Total Customer</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ number_format($stats['total']) }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-400">Baru Bulan Ini</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ number_format($stats['new_this_month']) }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-400">Pernah Belanjaa</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">{{ number_format($stats['buyers']) }}</h3>
                <p class="text-[10px] text-gray-400 font-semibold">
                    {{ $stats['total'] > 0 ? round($stats['buyers'] / $stats['total'] * 100) : 0 }}% dari total customer
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-rupiah-sign"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-400">Rata-rata Belanja</p>
                <h3 class="text-xl font-black text-gray-800 mt-0.5">Rp {{ number_format($stats['avg_per_buyer'], 0, ',', '.') }}</h3>
                <p class="text-[10px] text-gray-400 font-semibold">per customer yang bertransaksi</p>
            </div>
        </div>
    </div>

    {{-- ── Filter Tabs + Pencarian + Urutan ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4">

        {{-- Tabs --}}
        <div class="flex items-center gap-1.5 flex-wrap">
            @php
                $tabs = [
                    'all'      => ['label' => 'Semua',          'count' => $counts['all'],      'color' => 'bg-slate-900 text-white'],
                    'active'   => ['label' => 'Pernah Belanja', 'count' => $counts['active'],   'color' => 'bg-emerald-600 text-white'],
                    'inactive' => ['label' => 'Belum Belanja',  'count' => $counts['inactive'], 'color' => 'bg-amber-600 text-white'],
                    'new'      => ['label' => 'Baru (30 Hari)', 'count' => $counts['new'],      'color' => 'bg-blue-600 text-white'],
                    'blocked'  => ['label' => 'Diblokir',       'count' => $counts['blocked'],  'color' => 'bg-rose-600 text-white'],
                ];
            @endphp

            @foreach($tabs as $key => $tab)
                <a href="{{ route('admin.customers', array_merge(request()->except(['page', 'filter']), ['filter' => $key])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0
                   {{ $filter === $key ? $tab['color'] . ' shadow-sm' : 'text-gray-600 hover:bg-gray-100 bg-gray-50' }}">
                    <span>{{ $tab['label'] }}</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $filter === $key ? 'bg-white/20' : 'bg-gray-200 text-gray-700' }}">{{ $tab['count'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Pencarian + Urutan + Export --}}
        <form action="{{ route('admin.customers') }}" method="GET" class="flex items-center gap-2 shrink-0 flex-wrap">
            <input type="hidden" name="filter" value="{{ $filter }}">

            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama / email / telepon..."
                       class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 w-56">
            </div>

            <select name="sort" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-xl py-2 px-3 text-xs font-semibold text-gray-600 bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                <option value="latest" @selected($sort === 'latest')>Terbaru Mendaftar</option>
                <option value="oldest" @selected($sort === 'oldest')>Terlama Mendaftar</option>
                <option value="spend_desc" @selected($sort === 'spend_desc')>Belanja Terbesar</option>
                <option value="orders_desc" @selected($sort === 'orders_desc')>Pesanan Terbanyak</option>
                <option value="name" @selected($sort === 'name')>Nama (A–Z)</option>
            </select>

            <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold px-4 py-2 rounded-xl transition">Cari</button>

            @if(request('search'))
                <a href="{{ route('admin.customers', ['filter' => $filter]) }}"
                   class="text-xs font-bold px-3 py-2 rounded-xl text-gray-500 hover:bg-gray-100 transition" title="Hapus pencarian">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif

            <a href="{{ route('admin.customers.export', request()->except('page')) }}"
               class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold px-4 py-2 rounded-xl transition flex items-center gap-2">
                <i class="fa-solid fa-file-csv"></i>
                <span class="hidden sm:inline">Export</span>
            </a>
        </form>
    </div>

    {{-- ── Tabel Customer ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        @if($customers->isEmpty())
            <div class="p-16 text-center">
                <i class="fa-regular fa-user text-4xl text-gray-300 mb-4 block"></i>
                <h3 class="text-sm font-bold text-gray-500">Tidak ada customer yang cocok</h3>
                <p class="text-xs text-gray-400 mt-1">Coba ubah filter atau kata kunci pencarian.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500">
                    <thead class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 font-bold">Customer</th>
                            <th class="px-5 py-3.5 font-bold">Kontak</th>
                            <th class="px-5 py-3.5 font-bold text-center">Pesanan</th>
                            <th class="px-5 py-3.5 font-bold">Total Belanja</th>
                            <th class="px-5 py-3.5 font-bold">Terakhir Belanja</th>
                            <th class="px-5 py-3.5 font-bold">Status</th>
                            <th class="px-5 py-3.5 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($customers as $c)
                            @php
                                $spent = (float) ($c->total_spent ?? 0);
                                // Label loyalitas sederhana berdasar total belanja
                                $tier = $spent >= 5000000 ? ['VIP', 'bg-amber-100 text-amber-800']
                                      : ($spent >= 1000000 ? ['Loyal', 'bg-indigo-100 text-indigo-800']
                                      : ($c->orders_count > 0 ? ['Pembeli', 'bg-emerald-100 text-emerald-800']
                                      : ['Baru', 'bg-gray-100 text-gray-600']));

                                // Payload untuk modal intip cepat
                                $peek = [
                                    'name'       => $c->name,
                                    'email'      => $c->email,
                                    'phone'      => $c->phone ?: 'Belum diisi',
                                    'avatar'     => $c->avatar_url,
                                    'initial'    => strtoupper(substr($c->name, 0, 1)),
                                    'orders'     => $c->orders_count,
                                    'spent'      => 'Rp ' . number_format($spent, 0, ',', '.'),
                                    'tier'       => $tier[0],
                                    'blocked'    => (bool) $c->is_blocked,
                                    'registered' => $c->created_at->translatedFormat('d F Y'),
                                    'lastOrder'  => $c->last_order_at ? \Carbon\Carbon::parse($c->last_order_at)->translatedFormat('d F Y') : 'Belum pernah',
                                    'detailUrl'  => route('admin.customers.show', $c->id),
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition {{ $c->is_blocked ? 'bg-rose-50/40' : '' }}">
                                {{-- Identitas --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($c->avatar_url)
                                            <img src="{{ $c->avatar_url }}" alt="{{ $c->name }}"
                                                 class="w-10 h-10 rounded-full object-cover shrink-0 border border-gray-200">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-500 to-rose-500 flex items-center justify-center font-bold text-white text-sm shrink-0">
                                                {{ strtoupper(substr($c->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="font-bold text-gray-800 text-sm truncate">{{ $c->name }}</p>
                                                <span class="shrink-0 px-2 py-0.5 rounded-full text-[9px] font-black uppercase {{ $tier[1] }}">{{ $tier[0] }}</span>
                                            </div>
                                            <p class="text-[11px] text-gray-400 mt-0.5">Daftar {{ $c->created_at->translatedFormat('d M Y') }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kontak --}}
                                <td class="px-5 py-4">
                                    <p class="text-xs text-gray-700 truncate max-w-[220px]">{{ $c->email }}</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">
                                        <i class="fa-solid fa-phone text-[9px] mr-1"></i>{{ $c->phone ?: 'Belum diisi' }}
                                    </p>
                                </td>

                                {{-- Jumlah pesanan --}}
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-block min-w-[2rem] px-2.5 py-1 rounded-lg text-xs font-black
                                        {{ $c->orders_count > 0 ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-400' }}">
                                        {{ $c->orders_count }}
                                    </span>
                                </td>

                                {{-- Total belanja --}}
                                <td class="px-5 py-4">
                                    <p class="font-bold text-gray-800 text-xs">Rp {{ number_format($spent, 0, ',', '.') }}</p>
                                    @if($c->orders_count > 0)
                                        <p class="text-[10px] text-gray-400 mt-0.5">
                                            ≈ Rp {{ number_format($spent / $c->orders_count, 0, ',', '.') }} / pesanan
                                        </p>
                                    @endif
                                </td>

                                {{-- Terakhir belanja --}}
                                <td class="px-5 py-4">
                                    @if($c->last_order_at)
                                        <p class="text-xs text-gray-700">{{ \Carbon\Carbon::parse($c->last_order_at)->translatedFormat('d M Y') }}</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($c->last_order_at)->diffForHumans() }}</p>
                                    @else
                                        <span class="text-xs text-gray-300">—</span>
                                    @endif
                                </td>

                                {{-- Status akun --}}
                                <td class="px-5 py-4">
                                    @if($c->is_blocked)
                                        <span class="px-2.5 py-1 text-[10px] font-black rounded-full bg-rose-100 text-rose-700 uppercase">Diblokir</span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-black rounded-full bg-emerald-100 text-emerald-700 uppercase">Aktif</span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Intip cepat tanpa pindah halaman --}}
                                        <button type="button"
                                            @click="detail = {{ Js::from($peek) }}; showDetail = true"
                                            class="text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition"
                                            title="Intip cepat">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        <a href="{{ route('admin.customers.show', $c->id) }}"
                                           class="text-xs font-bold px-3 py-1.5 rounded-lg border border-blue-200 text-blue-600 hover:bg-blue-50 transition">
                                            <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i>Detail
                                        </a>

                                        <form action="{{ route('admin.customers.toggle-block', $c->id) }}" method="POST"
                                              onsubmit="return confirm('{{ $c->is_blocked ? 'Buka blokir akun ' : 'Blokir akun ' }}{{ addslashes($c->name) }}?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                class="text-xs font-bold px-3 py-1.5 rounded-lg border transition
                                                    {{ $c->is_blocked ? 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' : 'border-rose-200 text-rose-500 hover:bg-rose-50' }}"
                                                title="{{ $c->is_blocked ? 'Buka blokir' : 'Blokir akun' }}">
                                                <i class="fa-solid {{ $c->is_blocked ? 'fa-lock-open' : 'fa-ban' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════ --}}
    {{-- MODAL: Intip Cepat Customer --}}
    {{-- ════════════════════════════════ --}}
    <div x-show="showDetail" x-transition style="display:none;"
         class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md" @click.away="showDetail = false">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h2 class="font-black text-gray-800 text-sm uppercase tracking-wide">Ringkasan Customer</h2>
                <button @click="showDetail = false" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <div class="px-6 py-6">
                {{-- Identitas --}}
                <div class="flex items-center gap-4 mb-6">
                    <template x-if="detail.avatar">
                        <img :src="detail.avatar" class="w-14 h-14 rounded-full object-cover border border-gray-200">
                    </template>
                    <template x-if="!detail.avatar">
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-orange-500 to-rose-500 flex items-center justify-center font-black text-white text-xl"
                             x-text="detail.initial"></div>
                    </template>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="font-black text-gray-800 text-base truncate" x-text="detail.name"></h3>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-gray-100 text-gray-600" x-text="detail.tier"></span>
                        </div>
                        <p class="text-xs text-gray-500 truncate" x-text="detail.email"></p>
                        <p class="text-[11px] text-gray-400" x-text="detail.phone"></p>
                    </div>
                </div>

                {{-- Angka ringkas --}}
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="p-4 rounded-xl bg-blue-50 border border-blue-100">
                        <p class="text-[10px] text-blue-600 font-bold uppercase">Total Pesanan</p>
                        <p class="text-xl font-black text-blue-800 mt-1" x-text="detail.orders"></p>
                    </div>
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                        <p class="text-[10px] text-emerald-600 font-bold uppercase">Total Belanja</p>
                        <p class="text-sm font-black text-emerald-800 mt-1.5" x-text="detail.spent"></p>
                    </div>
                </div>

                <div class="space-y-2 text-xs mb-6">
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-gray-400 font-semibold">Terdaftar</span>
                        <span class="text-gray-700 font-bold" x-text="detail.registered"></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-gray-400 font-semibold">Belanja Terakhir</span>
                        <span class="text-gray-700 font-bold" x-text="detail.lastOrder"></span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-400 font-semibold">Status Akun</span>
                        <span class="font-black uppercase text-[10px] px-2 py-1 rounded-full"
                              :class="detail.blocked ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'"
                              x-text="detail.blocked ? 'Diblokir' : 'Aktif'"></span>
                    </div>
                </div>

                <a :href="detail.detailUrl"
                   class="block w-full text-center bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold px-6 py-3 rounded-xl shadow transition">
                    <i class="fa-solid fa-clock-rotate-left mr-1.5"></i>Lihat Riwayat Pesanan Lengkap
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
