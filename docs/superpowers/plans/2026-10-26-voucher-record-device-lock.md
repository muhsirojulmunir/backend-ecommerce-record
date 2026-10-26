# Voucher Promo RECORD (1x Akun & 1x Device) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membangun pembatasan penggunaan voucher promo "RECORD" maksimal 1 kali per akun dan 1 kali per perangkat fisik (device), lengkap dengan pencatatan log penggunaan dan antarmuka monitoring riwayat di Admin Kelola Voucher.

**Architecture:** Menggunakan pendekatan *hybrid device fingerprinting* di frontend (tersimpan di LocalStorage & Cookie) yang dikirim saat pengecekan dan checkout, divalidasi oleh backend Laravel terhadap tabel audit `voucher_usages` untuk memblokir multi-accounting, serta diintegrasikan dengan `DeviceDetector` untuk identifikasi perangkat di admin dashboard.

**Tech Stack:** PHP 8.2+ / 8.4+, Laravel 11, MySQL, Blade, Alpine.js, Tailwind CSS, Vanilla JS (Canvas & Browser Fingerprint).

## Global Constraints
- Database MySQL: `127.0.0.1:3306`, db `ecommerce_record`, user `root`, password `""`.
- Backend di `backend-ecommerce-record` dijalankan dengan `php artisan ...`.
- Frontend di `frontend-ecommerce-record` dijalankan dengan `herd php artisan ...`.
- Voucher game stopwatch dan voucher acak/batch lama (`type = single`) tetap berjalan normal tanpa mengalami regresi.
- Pembatasan 1x akun & 1x device khusus berlaku untuk voucher promo massal seperti `RECORD` (`type = promo`).

---

### Task 1: Database Migration (Vouchers & Voucher Usages)

**Files:**
- Create: `database/migrations/2026_10_26_000001_add_promo_columns_to_vouchers_table.php`
- Create: `database/migrations/2026_10_26_000002_create_voucher_usages_table.php`

**Interfaces:**
- Produces: Kolom `type`, `total_quota`, `used_count`, `limit_per_user`, `limit_per_device` di `vouchers`, serta tabel `voucher_usages`.

- [ ] **Step 1: Buat migration kolom promo pada tabel vouchers**
Create `database/migrations/2026_10_26_000001_add_promo_columns_to_vouchers_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('type', 20)->default('single')->after('code')->comment('single: kupon sekali pakai, promo: kode promo massal');
            $table->unsignedInteger('total_quota')->nullable()->after('amount')->comment('Batas kuota pemakaian promo (null = unlimited)');
            $table->unsignedInteger('used_count')->default(0)->after('total_quota')->comment('Total kali voucher telah digunakan');
            $table->unsignedSmallInteger('limit_per_user')->default(1)->after('used_count')->comment('Batas pemakaian per akun user');
            $table->unsignedSmallInteger('limit_per_device')->default(1)->after('limit_per_user')->comment('Batas pemakaian per perangkat (device)');

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'total_quota', 'used_count', 'limit_per_user', 'limit_per_device']);
        });
    }
};
```

- [ ] **Step 2: Buat migration tabel voucher_usages**
Create `database/migrations/2026_10_26_000002_create_voucher_usages_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('device_id', 64)->comment('Hash fingerprint perangkat client');
            $table->string('device_label', 120)->nullable()->comment('Label terdeteksi, cth: Windows 11 · Chrome');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->timestamps();

            $table->index(['voucher_id', 'device_id']);
            $table->index(['voucher_id', 'user_id']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_usages');
    }
};
```

- [ ] **Step 3: Jalankan migration**
Run: `php artisan migrate`
Expected: Migrations run successfully without errors.

---

### Task 2: Eloquent Models & Relations

**Files:**
- Create: `app/Models/VoucherUsage.php`
- Modify: `app/Models/Voucher.php`

**Interfaces:**
- Produces: `Voucher::usages()`, `Voucher::isPromo()`, `VoucherUsage` model dengan relasi `voucher()`, `user()`, `order()`.

- [ ] **Step 1: Buat model VoucherUsage**
Create `app/Models/VoucherUsage.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherUsage extends Model
{
    protected $fillable = [
        'voucher_id',
        'user_id',
        'order_id',
        'device_id',
        'device_label',
        'ip_address',
        'user_agent',
        'discount_amount',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'integer',
        ];
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
```

- [ ] **Step 2: Update model Voucher**
Tambahkan kolom baru ke `$fillable`, tambahkan relasi `usages()`, helper `isPromo()`, dan method pencatatan pemakaian:
In `app/Models/Voucher.php`:
- Tambahkan ke `$fillable`: `'type'`, `'total_quota'`, `'used_count'`, `'limit_per_user'`, `'limit_per_device'`.
- Tambahkan relasi:
```php
public function usages(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(VoucherUsage::class);
}

public function isPromo(): bool
{
    return $this->type === 'promo';
}
```

---

### Task 3: Backend Validation Service & Checkout Logic

**Files:**
- Modify: `app/Http/Controllers/Api/Customer/CustomerVoucherController.php`
- Modify: `app/Services/Customer/OrderService.php`

**Interfaces:**
- Consumes: `Request` dengan `code`, `device_id` opsional.
- Produces: JSON response yang memvalidasi batas akun dan perangkat untuk voucher promo, serta pencatatan audit log saat checkout.

- [ ] **Step 1: Update CustomerVoucherController@check**
Tambahkan validasi khusus untuk voucher promo:
- Tangkap `device_id = $request->input('device_id') ?? $request->header('X-Device-Id');`
- Cek apakah voucher bertipe `promo` (atau kodenya `RECORD`):
  - Jika `total_quota` terisi dan `used_count >= total_quota`, kembalikan 422: "Kuota voucher ini sudah habis."
  - Jika user login (`auth('sanctum')->check()` atau `auth()->check()`):
    - Cek apakah `user_id` sudah ada di `voucher_usages` untuk voucher ini:
      Kembalikan 422: "Akun Anda sudah pernah menggunakan voucher ini."
  - Jika `$deviceId` dikirim:
    - Cek apakah `device_id` sudah ada di `voucher_usages` untuk voucher ini:
      Kembalikan 422: "Perangkat ini sudah pernah menggunakan voucher {$voucher->code} (maksimal 1x per perangkat)."

- [ ] **Step 2: Update OrderService@checkout**
Pada langkah validasi voucher saat checkout:
- Terima `device_id` dari parameter `$data['device_id'] ?? null`.
- Jika voucher adalah promo:
  - Validasi ulang kuota (`used_count < total_quota`).
  - Validasi ulang apakah user sudah pernah pakai di `voucher_usages`.
  - Validasi ulang apakah device_id sudah pernah pakai di `voucher_usages`.
  - Setelah pesanan dibuat:
    - Buat `VoucherUsage::create([...])` dengan detail user, order, device_id, label via `DeviceDetector::detect($userAgent)['formatted']`, IP, dan discount_amount.
    - Increment `$voucherModel->increment('used_count');`
- Jika voucher bukan promo (`single`):
  - Jalankan alur lama `$voucherModel->markAsUsed($userId, $order->id);`

---

### Task 4: Frontend Device Fingerprint Script & Checkout Payload

**Files:**
- Create / Update script helper di Frontend layout: `frontend-ecommerce-record/resources/views/layouts/app.blade.php` atau file JS terpusat.
- Modify: Halaman Checkout / Keranjang di Frontend untuk mengirimkan `device_id` saat cek voucher dan saat submit checkout.

**Interfaces:**
- Produces: `window.RecordDevice.getDeviceId()` yang mengembalikan hash token unik yang persisten di `localStorage` dan `Cookie`.

- [ ] **Step 1: Buat generator Device ID persisten di frontend**
Script menghitung hash sederhana dari:
`[navigator.userAgent, screen.width, screen.height, screen.colorDepth, new Date().getTimezoneOffset(), navigator.language].join('###')`
dan memadukannya dengan UUID acak jika belum ada token di `localStorage` / `cookie`.
- [ ] **Step 2: Sertakan device_id saat pengecekan voucher dan submit checkout**
Pastikan form checkout / fetch call menyertakan field `device_id`.

---

### Task 5: Admin UI & Monitoring di Kelola Voucher

**Files:**
- Modify: `app/Http/Controllers/Web/AdminWebVoucherController.php`
- Modify: `resources/views/admin/vouchers.blade.php`

**Interfaces:**
- Produces: Form buat voucher promo "RECORD" di admin, badge tipe, dan tombol modal "Lihat Riwayat" untuk melihat data pemakaian per akun & perangkat.

- [ ] **Step 1: Update AdminWebVoucherController**
- Tambahkan method `storePromo(Request $request)` untuk menyimpan voucher promo (Kode, Nominal, Kuota, Kadaluarsa, Kategori).
- Tambahkan method `usages(Voucher $voucher)` (atau kirim relasi `usages.user`, `usages.order` ke view) untuk menampilkan log pemakaian.

- [ ] **Step 2: Update View admin/vouchers.blade.php**
- Tambahkan tab/pilihan di form: "Voucher Acak (Batch)" vs "Voucher Promo Massal (RECORD)".
- Di tabel voucher:
  - Tampilkan badge "Promo Massal" untuk voucher bertipe promo.
  - Tampilkan kuota pemakaian: `used_count / total_quota`.
  - Tambahkan tombol "Lihat Riwayat" yang membuka modal Alpine.js berisi tabel detail penggunaan (Nama User, No Order, Device Info, IP, Waktu).

---

### Task 6: Verifikasi & Git Commit

**Files:**
- All modified files in `backend-ecommerce-record` and `frontend-ecommerce-record`.

- [ ] **Step 1: Uji coba pembuatan voucher RECORD di admin**
- [ ] **Step 2: Uji coba klaim / checkout voucher RECORD 1x**
- [ ] **Step 3: Uji coba klaim ulang dengan akun berbeda di perangkat yang sama (harus ditolak)**
- [ ] **Step 4: Pastikan voucher game stopwatch tetap bekerja normal**
- [ ] **Step 5: Git commit di backend dan frontend**
Run: `git commit` di backend dan frontend dengan pesan yang rapi dan deskriptif.
