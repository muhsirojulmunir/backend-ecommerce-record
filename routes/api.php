<?php

use Illuminate\Support\Facades\Route;

// ─── Controller Namespaces ──────────────────────────────────────────────────
use App\Http\Controllers\Api\Auth\AdminAuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\BannerController;
use App\Http\Controllers\Api\Admin\DiscountController;
use App\Http\Controllers\Api\Admin\OrderController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\WebsiteSettingController;
use App\Http\Controllers\Api\Admin\ActivityLogController;

use App\Http\Controllers\Api\Customer\CustomerAuthController;
use App\Http\Controllers\Api\Customer\CustomerProductController;
use App\Http\Controllers\Api\Customer\CustomerBannerController;
use App\Http\Controllers\Api\Customer\CustomerCategoryController;
use App\Http\Controllers\Api\Customer\ShippingController;
use App\Http\Controllers\Api\Customer\CustomerOrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. PUBLIC API (Customer Browse & Shipping)
// ==========================================
Route::get('products', [CustomerProductController::class, 'index']);
Route::get('products/{slug}', [CustomerProductController::class, 'show']);
Route::get('banners', [CustomerBannerController::class, 'index']);
Route::get('categories', [CustomerCategoryController::class, 'index']);

Route::get('shipping/provinces', [ShippingController::class, 'provinces']);
Route::get('shipping/cities', [ShippingController::class, 'cities']);
Route::post('shipping/cost', [ShippingController::class, 'calculateCost']);


// ==========================================
// 2. CUSTOMER AUTHENTICATION & PORTAL API
// ==========================================
Route::prefix('customer')->group(function () {
    Route::post('register', [CustomerAuthController::class, 'register']);
    Route::post('login', [CustomerAuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('logout', [CustomerAuthController::class, 'logout']);
        Route::get('profile', [CustomerAuthController::class, 'profile']);

        // Orders & Checkout
        Route::post('orders', [CustomerOrderController::class, 'checkout']);
        Route::get('orders', [CustomerOrderController::class, 'index']);
        Route::get('orders/{id}', [CustomerOrderController::class, 'show']);
        Route::post('orders/{id}/cancel', [CustomerOrderController::class, 'requestReturn']);
    });
});


// ==========================================
// 3. SELLER CENTER (ADMIN & SUPER ADMIN) API
// ==========================================
Route::prefix('admin')->group(function () {
    // Public Login
    Route::post('auth/login', [AdminAuthController::class, 'login']);

    // Authenticated Admin Area
    Route::middleware(['auth:sanctum', 'log.admin'])->group(function () {
        
        // Admin Auth actions
        Route::post('auth/logout', [AdminAuthController::class, 'logout']);
        Route::get('auth/me', [AdminAuthController::class, 'me']);

        // --- HAK AKSES ADMIN & SUPER ADMIN ---
        
        // Kelola Produk
        Route::apiResource('products', ProductController::class);
        
        // Kelola Kategori
        Route::apiResource('categories', CategoryController::class);
        
        // Kelola Banner
        Route::apiResource('banners', BannerController::class);
        
        // Kelola Diskon
        Route::apiResource('discounts', DiscountController::class);
        
        // Kelola Pesanan & Returns
        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{id}', [OrderController::class, 'show']);
        Route::put('orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::post('returns/{id}/resolve', [OrderController::class, 'resolveReturn']);
        
        // Kelola Customer (Index/Show access for admin too, but write changes restricted by role middleware if wanted)
        Route::get('customers', [UserController::class, 'index']);
        Route::get('customers/{id}', [UserController::class, 'show']);

        // --- HAK AKSES KHUSUS SUPER ADMIN ---
        Route::middleware(['role:super_admin'])->group(function () {
            
            // Dashboard (Pesanan, log aktivitas)
            Route::get('dashboard', [DashboardController::class, 'index']);

            // Kelola Financial / Laporan
            Route::get('reports/sales', [ReportController::class, 'sales']);
            
            // Kelola Customer (Super Admin can perform CRUD)
            Route::post('customers', [UserController::class, 'store']);
            Route::put('customers/{id}', [UserController::class, 'update']);
            Route::delete('customers/{id}', [UserController::class, 'destroy']);

            // Settings, Role, Permission & Logs
            Route::get('settings', [WebsiteSettingController::class, 'index']);
            Route::put('settings', [WebsiteSettingController::class, 'update']);
            Route::get('activity-logs', [ActivityLogController::class, 'index']);
            
            Route::apiResource('roles', RoleController::class)->except(['show']);
            Route::get('permissions', [PermissionController::class, 'index']);
        });

    });
});
