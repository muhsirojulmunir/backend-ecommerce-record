<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Seller Center') - E-Commerce</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-record.png?v=2') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/favicon-record.png?v=2') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon-record.png?v=2') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Sembunyikan elemen Alpine.js sebelum Alpine selesai inisialisasi (cegah modal muncul sesaat saat refresh) */
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-slate-50 antialiased min-h-screen flex">

    <!-- Sidebar -->
    <aside id="sidebar"
        class="w-64 bg-slate-900 text-white flex flex-col justify-between shrink-0 border-r border-slate-800 fixed inset-y-0 left-0 z-40 -translate-x-full md:translate-x-0 transition-transform duration-300">
        <div class="flex flex-col h-full overflow-y-auto">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-slate-800 bg-slate-950 shrink-0">
                <i class="fa-solid fa-store text-orange-500 text-xl mr-3"></i>
                <span class="font-extrabold text-lg tracking-wider text-orange-400">SELLER CENTER</span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-0.5">

                {{-- â”€â”€â”€ Dashboard (Super Admin only)
                â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
                {{-- â”€â”€â”€ Dashboard (Super Admin only)
                â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
                @can('view dashboard')
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.dashboard') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-5 text-center text-base"></i>
                        <span>Dashboard</span>
                    </a>
                @endcan

                {{-- â”€â”€â”€ Grup Produk
                â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
                --}}
                @canany(['manage products', 'manage orders', 'manage banners', 'manage discounts'])
                    <p class="px-4 pt-5 pb-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Grup
                        Produk</p>

                    @can('manage orders')
                        <a href="{{ route('admin.orders') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.orders*') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-receipt w-5 text-center text-base"></i>
                            <span>Kelola Pesanan</span>
                        </a>
                    @endcan

                    @can('manage products')
                        <a href="{{ route('admin.products') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.products*') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-box-open w-5 text-center text-base"></i>
                            <span>Kelola Produk</span>
                        </a>
                    @endcan

                    @can('manage products')
                        <a href="{{ route('admin.categories') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.categories*') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-layer-group w-5 text-center text-base"></i>
                            <span>Kelola Kategori</span>
                        </a>
                    @endcan

                    @can('manage banners')
                        <a href="{{ route('admin.banners') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.banners*') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-images w-5 text-center text-base"></i>
                            <span>Kelola Banner</span>
                        </a>
                    @endcan

                    @can('manage discounts')
                        <a href="{{ route('admin.discounts') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.discounts*') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-tags w-5 text-center text-base"></i>
                            <span>Kelola Diskon</span>
                        </a>
                    @endcan
                @endcanany

                {{-- Grup Keuangan--}}
                @canany(['manage customers', 'view reports', 'manage returns', 'manage rpay', 'process withdrawals'])
                    <p class="px-4 pt-5 pb-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Grup
                        Keuangan</p>

                    @can('manage customers')
                        <a href="{{ route('admin.customers') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.customers*') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-users w-5 text-center text-base"></i>
                            <span>Kelola Customer</span>
                        </a>
                    @endcan

                    @can('view reports')
                        <a href="{{ route('admin.reports') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.reports*') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-chart-bar w-5 text-center text-base"></i>
                            <span>Kelola Laporan</span>
                        </a>
                    @endcan

                    @can('manage reviews')
                        @php
                            // Lencana menghitung ulasan bintang 1-2 yang masih tampil.
                            $ulasanRendah = \App\Models\ProductReview::where('is_hidden', false)
                                ->where('rating', '<=', 2)->count();
                        @endphp
                        <a href="{{ route('admin.reviews') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.reviews*') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-star w-5 text-center text-base"></i>
                            <span>Kelola Ulasan</span>
                            @if($ulasanRendah > 0)
                                <span
                                    class="ml-auto text-[10px] font-black bg-amber-500 text-white rounded-full px-2 py-0.5">{{ $ulasanRendah }}</span>
                            @endif
                        </a>
                    @endcan

                    @can('manage returns')
                        @php
                            // Jumlah pengajuan yang belum ditinjau, ditampilkan sebagai
                            // lencana supaya tidak ada pengajuan yang terlewat berhari-hari.
                            $returPending = \App\Models\OrderReturn::where('type', 'return')->where('status', 'pending')->count();
                        @endphp
                        <a href="{{ route('admin.returns') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.returns*') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-rotate-left w-5 text-center text-base"></i>
                            <span>Kelola Pengembalian</span>
                            @if($returPending > 0)
                                <span
                                    class="ml-auto text-[10px] font-black bg-red-500 text-white rounded-full px-2 py-0.5">{{ $returPending }}</span>
                            @endif
                        </a>
                    @endcan

                    @can('manage rpay')
                        <a href="{{ route('admin.rpay') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.rpay') || request()->routeIs('admin.rpay.show') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-wallet w-5 text-center text-base"></i>
                            <span>Kelola R_Pay</span>
                        </a>
                    @endcan

                    @can('process withdrawals')
                        @php
                            $cairPending = \App\Models\RpayWithdrawal::whereIn('status', ['pending', 'processing'])->count();
                        @endphp
                        <a href="{{ route('admin.rpay.withdrawals') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.rpay.withdrawals*') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-money-bill-transfer w-5 text-center text-base"></i>
                            <span>Pencairan R_Pay</span>
                            @if($cairPending > 0)
                                <span
                                    class="ml-auto text-[10px] font-black bg-red-500 text-white rounded-full px-2 py-0.5">{{ $cairPending }}</span>
                            @endif
                        </a>
                    @endcan
                @endcanany

                {{-- â”€â”€â”€ Grup Settings (Super Admin only) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
                @canany(['manage roles', 'manage permissions', 'manage settings', 'view activity logs'])
                    <p class="px-4 pt-5 pb-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Settings
                    </p>

                    @can('manage roles')
                        <a href="{{ route('admin.roles') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.roles*') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-shield-halved w-5 text-center text-base"></i>
                            <span>Kelola Role</span>
                        </a>
                    @endcan

                    @can('manage permissions')
                        <a href="{{ route('admin.permissions') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.permissions*') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-key w-5 text-center text-base"></i>
                            <span>Kelola Permission</span>
                        </a>
                    @endcan

                    @can('manage settings')
                        <a href="{{ route('admin.settings') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.settings*') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-gear w-5 text-center text-base"></i>
                            <span>Pengaturan Website</span>
                        </a>
                    @endcan

                    @can('view activity logs')
                        <a href="{{ route('admin.activity-logs') }}"
                            class="flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-150 gap-3 {{ request()->routeIs('admin.activity-logs*') ? 'bg-orange-600 text-white shadow-md shadow-orange-900/40' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fa-solid fa-clock-rotate-left w-5 text-center text-base"></i>
                            <span>Log Aktivitas</span>
                        </a>
                    @endcan
                @endcanany

            </nav>

            <!-- User Info & Logout -->
            <div class="p-3 border-t border-slate-800 bg-slate-950/60 shrink-0">
                <div class="flex items-center justify-between px-2 py-2">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-full bg-gradient-to-br from-orange-500 to-rose-500 flex items-center justify-center font-bold text-white shadow-md text-sm">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-sm font-bold text-white truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                            <p class="text-[10px] text-slate-400 capitalize font-medium">
                                {{ Auth::user()->isSuperAdmin() ? 'Super Admin' : 'Admin' }}
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="text-slate-500 hover:text-red-400 transition p-1.5 rounded-lg hover:bg-slate-800"
                            title="Keluar">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen md:ml-64">
        <!-- Top Header -->
        <header
            class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 sticky top-0 z-30 shadow-sm">
            <div class="flex items-center gap-4">
                <!-- Mobile Hamburger -->
                <button onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full')"
                    class="md:hidden text-gray-500 hover:text-gray-800 transition">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>

                <div>
                    <h1 class="text-lg font-bold text-gray-800">@yield('page_title', 'Dashboard')</h1>
                    <p class="text-xs text-gray-400 hidden sm:block">@yield('page_subtitle', '')</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 text-sm text-gray-500">
                    <div
                        class="w-7 h-7 rounded-full bg-gradient-to-br from-orange-500 to-rose-500 flex items-center justify-center font-bold text-white text-xs">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <span class="font-medium text-gray-700">{{ Auth::user()->name ?? 'Admin' }}</span>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-6 md:p-8 bg-slate-50">
            <x-peringatan-saldo />

            @if(session('success'))
                <div
                    class="flash-alert mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm flex items-center justify-between shadow-sm transition-all duration-500 overflow-hidden">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-green-600 text-base"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.closest('.flash-alert').remove()"
                        class="text-green-600 hover:text-green-800 transition p-1">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            @endif
            @if(session('error'))
                {{-- Tanpa kelas "flash-alert": pesan galat TIDAK ikut hilang
                sendiri setelah 5 detik. Laporan kegagalan pengiriman
                menyebut nomor pesanan dan alasannya satu per satu â€” kalau
                lenyap sebelum sempat dibaca, admin tidak tahu pesanan mana
                yang perlu diulang. Ditutup sendiri oleh admin. --}}
                <div
                    class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center justify-between shadow-sm transition-all duration-500 overflow-hidden">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-exclamation text-red-500 text-base"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.closest('div').parentElement.remove()"
                        class="text-red-500 hover:text-red-700 transition p-1">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            @endif
            @if(session('info'))
                <div
                    class="mb-6 p-4 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 text-sm flex items-start gap-3 shadow-sm">
                    <i class="fa-solid fa-circle-info text-sky-500 text-base mt-0.5"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="py-3 px-8 bg-white border-t border-gray-100 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} E-Commerce Seller Center &mdash; Hak Cipta Dilindungi.
        </footer>
    </div>

    <!-- Sidebar Overlay (mobile) -->
    <div onclick="document.getElementById('sidebar').classList.add('-translate-x-full')"
        class="md:hidden fixed inset-0 bg-black/50 z-30 hidden" id="sidebar-overlay"></div>

    <!-- Alarm (5 Seconds) -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.flash-alert');
            alerts.forEach(function (alert) {
                setTimeout(function () {
                    alert.style.transition = 'all 0.5s ease-out';
                    alert.style.opacity = '0';
                    alert.style.maxHeight = '0px';
                    alert.style.marginBottom = '0px';
                    alert.style.paddingTop = '0px';
                    alert.style.paddingBottom = '0px';
                    setTimeout(function () {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>

</html>