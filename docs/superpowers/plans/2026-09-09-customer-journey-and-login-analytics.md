# Pelacakan Customer Journey, Detail Produk, dan Analitik Login Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengimplementasikan pelacakan customer journey lengkap (detail produk dilihat, pencarian, keranjang, checkout, login/register) di frontend dengan informasi perangkat (Mobile/Desktop, IP, browser), serta menghadirkan metrik analitik login dan perbandingan perangkat di backend admin log aktivitas untuk evaluasi website toko online.

**Architecture:** 
Frontend menggunakan helper `CatatAktivitas` dengan metadata perangkat otomatis (`DeviceDetector` / parser) yang dipanggil pada titik controller utama e-commerce (`ProductController`, `CartController`, `CheckoutController`, `AuthenticatedSessionController`, `RegisteredUserController`). Backend `AdminWebActivityLogController` mengagregasikan data log untuk menghitung total login, perbandingan admin vs customer, persentase mobile vs desktop, produk terpopuler, dan kata kunci teratas, lalu disajikan dalam 4 kartu evaluasi modern pada `activity-logs.blade.php`.

**Tech Stack:** Laravel 10/11 (PHP 8.2 backend / PHP 8.4 frontend), Spatie Activitylog (MySQL `activity_log`), Tailwind CSS, Alpine.js.

## Global Constraints
- Backend: PHP 8.2.12 (`php artisan ...`).
- Frontend: PHP >= 8.4.1 (`herd php artisan ...`).
- Database: MySQL lokal `127.0.0.1:3306`, database `ecommerce_record`, tabel bersama `activity_log`.
- Log tamu (*guest*) memiliki `causer_id = null`, wajib terkelompokkan ke dalam Tab "User & Tamu" di backend.
- Pencegah spam: Kunjungan produk yang sama di-throttle via sesi (5 menit).

---

### Task 1: Enhancement Helper CatatAktivitas Frontend
**Files:**
- Modify: `../frontend-ecommerce-record/app/Support/CatatAktivitas.php`

**Interfaces:**
- Produces: `CatatAktivitas::tulisProdukView($product, $request)` dengan session guard 5 menit.
- Produces: `CatatAktivitas::tulisPencarian($keyword, $count, $request)`

- [ ] **Step 1: Tambahkan method khusus di CatatAktivitas untuk product view & search dengan throttle**
- [ ] **Step 2: Jalankan linting PHP pada CatatAktivitas.php**
  Run: `herd php -l app/Support/CatatAktivitas.php` (di folder frontend)
- [ ] **Step 3: Commit Task 1 di repo frontend**

---

### Task 2: Pelacakan Customer Journey di Controller Frontend
**Files:**
- Modify: `../frontend-ecommerce-record/app/Http/Controllers/ProductController.php`
- Modify: `../frontend-ecommerce-record/app/Http/Controllers/CartController.php`
- Modify: `../frontend-ecommerce-record/app/Http/Controllers/CheckoutController.php`
- Modify: `../frontend-ecommerce-record/app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- Modify: `../frontend-ecommerce-record/app/Http/Controllers/Auth/RegisteredUserController.php`

- [ ] **Step 1: Tambahkan pelacakan di ProductController (show untuk produk detail, index untuk search)**
- [ ] **Step 2: Tambahkan pelacakan di CartController (store untuk add to cart, destroy untuk remove)**
- [ ] **Step 3: Tambahkan pelacakan di CheckoutController (index untuk buka checkout, shipping-cost untuk pilih kurir)**
- [ ] **Step 4: Tambahkan pelacakan login, register, dan logout di AuthenticatedSessionController & RegisteredUserController**
- [ ] **Step 5: Linting semua controller frontend yang diubah**
  Run: `php -l` pada masing-masing controller
- [ ] **Step 6: Commit Task 2 di repo frontend**

---

### Task 3: Pelacakan Login & Logout Admin di Backend
**Files:**
- Modify: `backend-ecommerce-record/app/Http/Controllers/Web/AdminWebAuthController.php`

- [ ] **Step 1: Tambahkan pencatatan activity log pada login berhasil dan logout di AdminWebAuthController**
- [ ] **Step 2: Linting AdminWebAuthController.php**
  Run: `php -l app/Http/Controllers/Web/AdminWebAuthController.php`
- [ ] **Step 3: Commit Task 3 di repo backend**

---

### Task 4: Agregasi Analitik Login & Perangkat di AdminWebActivityLogController
**Files:**
- Modify: `backend-ecommerce-record/app/Http/Controllers/Web/AdminWebActivityLogController.php`

- [ ] **Step 1: Perluas SHOP_LOG_NAMES dengan `produk`, `pencarian`, `auth`**
- [ ] **Step 2: Buat method `analytics()` untuk menghitung total login (hari ini, 7 hari, all), admin vs customer, mobile vs desktop ratio, top 3 viewed products, top 3 searches**
- [ ] **Step 3: Kirim data analitik ke view `admin.activity-logs`**
- [ ] **Step 4: Linting AdminWebActivityLogController.php**
  Run: `php -l app/Http/Controllers/Web/AdminWebActivityLogController.php`
- [ ] **Step 5: Commit Task 4 di repo backend**

---

### Task 5: Desain UI Kartu Metrik Evaluasi & Detail Modal di Blade
**Files:**
- Modify: `backend-ecommerce-record/resources/views/admin/activity-logs.blade.php`

- [ ] **Step 1: Tambahkan 4 Kartu Metrik Evaluasi di atas tabel (Total Login, Mobile vs Desktop, Top Viewed Products, Top Searches)**
- [ ] **Step 2: Tambahkan styling badge & icon untuk modul `auth`, `produk`, `pencarian`, `keranjang`, `checkout`**
- [ ] **Step 3: Perbarui modal detail Alpine.js untuk menampilkan info spesifik produk dan pencarian**
- [ ] **Step 4: Verifikasi kompilasi Blade**
  Run: `php artisan view:cache` lalu `php artisan view:clear`
- [ ] **Step 5: Commit Task 5 di repo backend**

---

### Task 6: Verifikasi End-to-End dan Git Push
- [ ] **Step 1: Jalankan simulasi aksi produk view, search, dan login untuk memvalidasi pencatatan log**
- [ ] **Step 2: Verifikasi tampilan dashboard analitik di backend**
- [ ] **Step 3: Git push origin main untuk kedua repositori (backend & frontend)**
