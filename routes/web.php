<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AdminWebAuthController;
use App\Http\Controllers\Web\AdminWebDashboardController;
use App\Http\Controllers\Web\AdminWebProductController;
use App\Http\Controllers\Web\AdminWebProductImportController;
use App\Http\Controllers\Web\AdminWebCategoryController;
use App\Http\Controllers\Web\AdminWebDiscountController;
use App\Http\Controllers\Web\AdminWebBannerController;
use App\Http\Controllers\Web\AdminWebOrderController;
use App\Http\Controllers\Web\AdminWebOrderExportController;
use App\Http\Controllers\Web\AdminWebPelacakanController;
use App\Http\Controllers\Web\AdminWebCustomerController;
use App\Http\Controllers\Web\AdminWebReportController;
use App\Http\Controllers\Web\AdminWebReturnController;
use App\Http\Controllers\Web\AdminWebReviewController;
use App\Http\Controllers\Web\AdminWebRpayController;
use App\Http\Controllers\Web\AdminWebSaldoBiteshipController;
use App\Http\Controllers\Web\AdminWebRoleController;
use App\Http\Controllers\Web\AdminWebPermissionController;
use App\Http\Controllers\Web\AdminWebSettingController;
use App\Http\Controllers\Web\AdminWebActivityLogController;

// Halaman Utama Langsung Tampilkan Form Login (Instant, Tanpa Delay Redirect)
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->can('view dashboard')) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->can('manage products')) {
            return redirect()->route('admin.products');
        }
    }
    return app(AdminWebAuthController::class)->showLoginForm();
});

Route::post('/login', [AdminWebAuthController::class, 'login']);

// ─── Admin Panel Web Routes (Seller Center UI) ─────────────────────────────
Route::prefix('admin')->group(function () {

    // Guest/Auth Landing Route
    Route::get('/', function () {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        if ($user->can('view dashboard')) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->can('manage products')) {
            return redirect()->route('admin.products');
        }
        
        return redirect()->route('login');
    });

    // Guest: Login
    Route::get('/login', [AdminWebAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminWebAuthController::class, 'login'])->name('admin.login.submit');

    // Authenticated routes
    Route::middleware(['auth'])->group(function () {

        Route::post('/logout', [AdminWebAuthController::class, 'logout'])->name('admin.logout');

        // ── Super Admin only: Dashboard ─────────────────────────────────────
        Route::get('/dashboard', [AdminWebDashboardController::class, 'index'])
            ->middleware('can:view dashboard')
            ->name('admin.dashboard');

        // ── Grup Produk ─────────────────────────────────────────────────────

        Route::prefix('products')->middleware('can:manage products')->group(function () {
            Route::get('/', [AdminWebProductController::class, 'index'])->name('admin.products');

            // Impor massal via Excel/CSV — didaftarkan sebelum /{id} agar tidak tertangkap route model
            Route::get('/import', [AdminWebProductImportController::class, 'index'])->name('admin.products.import');
            Route::get('/import/template', [AdminWebProductImportController::class, 'template'])->name('admin.products.import.template');
            Route::post('/import/preview', [AdminWebProductImportController::class, 'preview'])->name('admin.products.import.preview');
            Route::post('/import', [AdminWebProductImportController::class, 'store'])->name('admin.products.import.store');
            Route::post('/import/cancel', [AdminWebProductImportController::class, 'cancel'])->name('admin.products.import.cancel');

            Route::get('/create', [AdminWebProductController::class, 'create'])->name('admin.products.create');
            Route::post('/', [AdminWebProductController::class, 'store'])->name('admin.products.store');
            Route::get('/{id}/edit', [AdminWebProductController::class, 'edit'])->name('admin.products.edit');
            Route::match(['PUT', 'POST'], '/{id}', [AdminWebProductController::class, 'update'])->name('admin.products.update');
            Route::patch('/{id}/unggulan', [AdminWebProductController::class, 'toggleFeatured'])
                ->whereNumber('id')->name('admin.products.toggle-featured');

            // Ubah stok satu varian langsung dari daftar produk, tanpa
            // membuka borang ubah produk.
            Route::patch('/varian/{id}/stok', [AdminWebProductController::class, 'updateVariantStock'])
                ->whereNumber('id')->name('admin.products.variant-stock');
            Route::delete('/{id}', [AdminWebProductController::class, 'destroy'])->name('admin.products.destroy');
        });

        // Categories (Kelola Kategori) — ikut izin kelola produk
        Route::prefix('categories')->middleware('can:manage products')->group(function () {
            Route::get('/', [AdminWebCategoryController::class, 'index'])->name('admin.categories');
            Route::post('/', [AdminWebCategoryController::class, 'store'])->name('admin.categories.store');
            Route::put('/{id}', [AdminWebCategoryController::class, 'update'])->whereNumber('id')->name('admin.categories.update');
            Route::patch('/{id}/toggle', [AdminWebCategoryController::class, 'toggle'])->whereNumber('id')->name('admin.categories.toggle');
            Route::patch('/{id}/reorder', [AdminWebCategoryController::class, 'reorder'])->whereNumber('id')->name('admin.categories.reorder');
            Route::delete('/{id}', [AdminWebCategoryController::class, 'destroy'])->whereNumber('id')->name('admin.categories.destroy');
        });

        // Banners
        Route::prefix('banners')->middleware('can:manage banners')->group(function () {
            Route::get('/', [AdminWebBannerController::class, 'index'])->name('admin.banners');
            Route::post('/', [AdminWebBannerController::class, 'store'])->name('admin.banners.store');
            Route::put('/{banner}', [AdminWebBannerController::class, 'update'])->name('admin.banners.update');
            Route::patch('/{banner}/toggle', [AdminWebBannerController::class, 'toggle'])->name('admin.banners.toggle');
            Route::delete('/{banner}', [AdminWebBannerController::class, 'destroy'])->name('admin.banners.destroy');
        });

        // Discounts
        Route::prefix('discounts')->middleware('can:manage discounts')->group(function () {
            Route::get('/', [AdminWebDiscountController::class, 'index'])->name('admin.discounts');
            Route::put('/{productId}', [AdminWebDiscountController::class, 'update'])->name('admin.discounts.update');
            Route::patch('/{productId}/toggle', [AdminWebDiscountController::class, 'toggle'])->name('admin.discounts.toggle');
            Route::delete('/{productId}', [AdminWebDiscountController::class, 'destroy'])->name('admin.discounts.destroy');
            Route::post('/bulk', [AdminWebDiscountController::class, 'bulkUpdate'])->name('admin.discounts.bulk');
        });

        // Orders (Kelola Pesanan)
        Route::prefix('orders')->middleware('can:manage orders')->group(function () {
            Route::get('/', [AdminWebOrderController::class, 'index'])->name('admin.orders');
            Route::get('/{id}', [AdminWebOrderController::class, 'show'])->name('admin.orders.show');
            Route::put('/{id}/status', [AdminWebOrderController::class, 'updateStatus'])->name('admin.orders.update-status');
            Route::put('/{id}/tracking', [AdminWebOrderController::class, 'updateTracking'])->name('admin.orders.update-tracking');
            Route::patch('/{id}/confirm-payment', [AdminWebOrderController::class, 'confirmPayment'])->name('admin.orders.confirm-payment');
            // Aksi Massal
            Route::post('/bulk/confirm-payment', [AdminWebOrderController::class, 'bulkConfirmPayment'])->name('admin.orders.bulk-confirm-payment');
            Route::post('/bulk/ship', [AdminWebOrderController::class, 'bulkShip'])->name('admin.orders.bulk-ship');
            Route::post('/bulk/print', [AdminWebOrderController::class, 'bulkPrint'])->name('admin.orders.bulk-print');
            Route::post('/bulk/delete', [AdminWebOrderController::class, 'bulkDelete'])->name('admin.orders.bulk-delete');
            // Hapus Pesanan (Khusus Super Admin)
            Route::delete('/{id}', [AdminWebOrderController::class, 'destroy'])->whereNumber('id')->name('admin.orders.destroy');
        });

        // ── Grup Keuangan ───────────────────────────────────────────────────

        // Customers (Kelola Customer)
        Route::prefix('customers')->middleware('can:manage customers')->group(function () {
            Route::get('/', [AdminWebCustomerController::class, 'index'])->name('admin.customers');
            Route::get('/export', [AdminWebCustomerController::class, 'export'])->name('admin.customers.export');
            Route::get('/{id}', [AdminWebCustomerController::class, 'show'])->whereNumber('id')->name('admin.customers.show');
            Route::patch('/{id}/toggle-block', [AdminWebCustomerController::class, 'toggleBlock'])->whereNumber('id')->name('admin.customers.toggle-block');
        });

        // Ekspor pesanan ke XLSX berikut riwayat unduhannya.
        Route::prefix('orders/ekspor')->middleware('can:manage orders')->group(function () {
            Route::post('/', [AdminWebOrderExportController::class, 'store'])->name('admin.orders.ekspor');
            Route::get('/{id}/unduh', [AdminWebOrderExportController::class, 'download'])
                ->whereNumber('id')->name('admin.orders.ekspor.unduh');
            Route::delete('/{id}', [AdminWebOrderExportController::class, 'destroy'])
                ->whereNumber('id')->name('admin.orders.ekspor.hapus');
        });

        // Pelacakan paket, dua arah: keluar ke pembeli dan kembali ke toko.
        // Dipanggil lewat AJAX dari halaman rincian pesanan dan pengembalian.
        // Saldo Biteship — dicatat manual karena Biteship tidak menyediakan
        // endpoint untuk membacanya.
        Route::prefix('saldo-biteship')->middleware('can:manage orders')->group(function () {
            Route::get('/', [AdminWebSaldoBiteshipController::class, 'index'])->name('admin.saldo-biteship');
            Route::post('/', [AdminWebSaldoBiteshipController::class, 'store'])->name('admin.saldo-biteship.simpan');
        });

        Route::prefix('pelacakan')->group(function () {
            Route::get('/pesanan/{id}', [AdminWebPelacakanController::class, 'pesanan'])
                ->whereNumber('id')->middleware('can:manage orders')->name('admin.pelacakan.pesanan');
            Route::get('/pengembalian/{id}', [AdminWebPelacakanController::class, 'pengembalian'])
                ->whereNumber('id')->middleware('can:manage returns')->name('admin.pelacakan.pengembalian');
        });

        // Ulasan produk (Kelola Ulasan)
        Route::prefix('reviews')->middleware('can:manage reviews')->group(function () {
            Route::get('/', [AdminWebReviewController::class, 'index'])->name('admin.reviews');
            Route::patch('/{id}/toggle', [AdminWebReviewController::class, 'toggle'])->whereNumber('id')->name('admin.reviews.toggle');
            Route::delete('/{id}', [AdminWebReviewController::class, 'destroy'])->whereNumber('id')->name('admin.reviews.destroy');
        });

        // Pengembalian Barang (Kelola Pengembalian)
        Route::prefix('returns')->middleware('can:manage returns')->group(function () {
            Route::get('/', [AdminWebReturnController::class, 'index'])->name('admin.returns');
            Route::get('/{id}', [AdminWebReturnController::class, 'show'])->whereNumber('id')->name('admin.returns.show');
            Route::post('/{id}/decide', [AdminWebReturnController::class, 'decide'])->whereNumber('id')->name('admin.returns.decide');
            Route::post('/{id}/terima', [AdminWebReturnController::class, 'terima'])->whereNumber('id')->name('admin.returns.terima');
            Route::post('/{id}/finalize', [AdminWebReturnController::class, 'finalize'])->whereNumber('id')->name('admin.returns.finalize');
        });

        // R_Pay — dompet digital pembeli
        Route::prefix('rpay')->group(function () {
            Route::get('/', [AdminWebRpayController::class, 'index'])
                ->middleware('can:manage rpay')->name('admin.rpay');
            Route::get('/export', [AdminWebRpayController::class, 'export'])
                ->middleware('can:manage rpay')->name('admin.rpay.export');

            // Antrean pencairan boleh diproses admin maupun akun management,
            // jadi hak aksesnya dipisah dari sekadar melihat saldo.
            Route::get('/pencairan', [AdminWebRpayController::class, 'withdrawals'])
                ->middleware('can:process withdrawals')->name('admin.rpay.withdrawals');
            Route::post('/pencairan/{id}', [AdminWebRpayController::class, 'processWithdrawal'])
                ->whereNumber('id')->middleware('can:process withdrawals')->name('admin.rpay.withdrawals.process');

            Route::get('/{id}', [AdminWebRpayController::class, 'show'])
                ->whereNumber('id')->middleware('can:manage rpay')->name('admin.rpay.show');
        });

        // Reports (Kelola Laporan, Super Admin only)
        Route::prefix('reports')->middleware('can:view reports')->group(function () {
            Route::get('/', [AdminWebReportController::class, 'index'])->name('admin.reports');
            Route::get('/export', [AdminWebReportController::class, 'export'])->name('admin.reports.export');
        });

        // ── Grup Settings (Super Admin only) ───────────────────────────────

        // Roles (Kelola Role)
        Route::prefix('roles')->middleware('can:manage roles')->group(function () {
            Route::get('/', [AdminWebRoleController::class, 'index'])->name('admin.roles');
            Route::post('/', [AdminWebRoleController::class, 'store'])->name('admin.roles.store');
            Route::get('/{id}/users', [AdminWebRoleController::class, 'users'])->whereNumber('id')->name('admin.roles.users');
            Route::put('/{id}', [AdminWebRoleController::class, 'update'])->whereNumber('id')->name('admin.roles.update');
            Route::delete('/{id}', [AdminWebRoleController::class, 'destroy'])->whereNumber('id')->name('admin.roles.destroy');
        });

        // Permissions (Kelola Permission)
        Route::prefix('permissions')->middleware('can:manage permissions')->group(function () {
            Route::get('/', [AdminWebPermissionController::class, 'index'])->name('admin.permissions');
            Route::post('/', [AdminWebPermissionController::class, 'store'])->name('admin.permissions.store');
            Route::post('/matrix', [AdminWebPermissionController::class, 'syncMatrix'])->name('admin.permissions.matrix');
            Route::put('/{id}', [AdminWebPermissionController::class, 'update'])->whereNumber('id')->name('admin.permissions.update');
            Route::delete('/{id}', [AdminWebPermissionController::class, 'destroy'])->whereNumber('id')->name('admin.permissions.destroy');
        });

        // Website Settings (Pengaturan Website)
        Route::prefix('settings')->middleware('can:manage settings')->group(function () {
            Route::get('/', [AdminWebSettingController::class, 'index'])->name('admin.settings');
            Route::post('/', [AdminWebSettingController::class, 'update'])->name('admin.settings.update');
        });

        // Activity Logs (Log Aktivitas)
        Route::prefix('activity-logs')->middleware('can:view activity logs')->group(function () {
            Route::get('/', [AdminWebActivityLogController::class, 'index'])->name('admin.activity-logs');
            Route::get('/export', [AdminWebActivityLogController::class, 'export'])->name('admin.activity-logs.export');
            Route::delete('/prune', [AdminWebActivityLogController::class, 'prune'])->name('admin.activity-logs.prune');
        });
    });
});



