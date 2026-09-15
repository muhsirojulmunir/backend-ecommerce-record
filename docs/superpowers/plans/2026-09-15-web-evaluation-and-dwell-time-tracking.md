# Pelacakan Durasi Atensi (Dwell Time) & Evaluasi Seksi Web Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Mengimplementasikan pelacakan durasi atensi (*dwell time*) pengunjung pada seluruh seksi strategis website e-commerce (baik user login maupun tamu/guest) menggunakan `IntersectionObserver`, serta menghadirkan metrik analitik atensi seksi website di dashboard admin `admin.recordshoes.com` (`/admin/activity-logs`) untuk evaluasi performa website.

**Architecture:**
1. Di **frontend**, elemen seksi ditandai dengan atribut `data-track-section` dan `data-track-label`. Skrip client-side berbasis `IntersectionObserver` dan `document.visibilityState` menghitung durasi aktif (minimal 3 detik) dan mengirimkan data akumulatif secara berkala (tiap 45 detik) atau saat exit via beacon ke endpoint `POST /track/dwell`.
2. Endpoint `TrackingController::recordDwell` mencatat data ke tabel `activity_log` dengan `log_name = 'evaluasi_web'` dan metadata lengkap (durasi detik, nama seksi, halaman, IP, dan perangkat).
3. Di **backend**, `AdminWebActivityLogController` mengagregasi data durasi dan frekuensi seksi dalam 7 hari terakhir (menghitung total waktu, rata-rata durasi per visit, frekuensi tayang, dan % pangsa atensi), lalu menyajikannya dalam **Kartu Analitik Evaluasi Seksi Web** dan baris log interaktif pada `activity-logs.blade.php`.

**Tech Stack:** Laravel 10/11 (PHP 8.2 backend / PHP 8.4 frontend), Spatie Activitylog (MySQL `activity_log`), Vanilla JS (`IntersectionObserver`, `sendBeacon`), Tailwind CSS, Alpine.js.

## Global Constraints
- Backend: PHP 8.2.12 (`php artisan ...`).
- Frontend: PHP >= 8.4.1 (`herd php artisan ...`).
- Database: MySQL lokal `127.0.0.1:3306`, database `ecommerce_record`, tabel bersama `activity_log`.
- Hanya mencatat jika pengunjung stay minimal 3 detik di seksi tersebut (filter scroll cepat / anti-spam).
- Waktu hanya berjalan saat tab browser aktif (`document.visibilityState === 'visible'`).
- CSRF exception untuk endpoint `track/dwell` pada `bootstrap/app.php` frontend agar `sendBeacon` selalu berhasil saat exit.
- Tidak menyentuh atau mengubah fitur lain yang tidak berkaitan.

---

### Task 1: Endpoint Penerima & Controller Pelacakan Dwell Time di Frontend
**Files:**
- Create: `../frontend-ecommerce-record/app/Http/Controllers/TrackingController.php`
- Modify: `../frontend-ecommerce-record/routes/web.php`
- Modify: `../frontend-ecommerce-record/bootstrap/app.php`

**Interfaces:**
- Produces: `POST /track/dwell` menerima JSON:
  `{ section_id: string, section_label: string, page_name: string, page_url: string, duration_seconds: int }`
- Produces: `CatatAktivitas::tulis('evaluasi_web', ...)` dengan properti durasi dan perangkat.

- [ ] **Step 1: Daftarkan CSRF exception `track/dwell` di `bootstrap/app.php` frontend**
- [ ] **Step 2: Buat `TrackingController.php` dengan method `recordSectionDwell`**
- [ ] **Step 3: Daftarkan route `POST /track/dwell` di `routes/web.php` frontend**
- [ ] **Step 4: Linting PHP syntax `TrackingController.php`**
  Run: `php -l app/Http/Controllers/TrackingController.php` (di folder frontend)
- [ ] **Step 5: Commit Task 1 di repo frontend**

---

### Task 2: Penandaan Seksi Pelacakan & Skrip Client-Side Dwell Tracker di Frontend
**Files:**
- Modify: `../frontend-ecommerce-record/resources/views/home.blade.php`
- Modify: `../frontend-ecommerce-record/resources/views/affiliate/index.blade.php`
- Modify: `../frontend-ecommerce-record/resources/views/products/show.blade.php`
- Modify: `../frontend-ecommerce-record/resources/views/cart/index.blade.php`
- Modify: `../frontend-ecommerce-record/resources/views/checkout/index.blade.php`
- Modify: `../frontend-ecommerce-record/resources/views/layouts/app.blade.php`

**Interfaces:**
- Produces: Tag atribut `data-track-section` & `data-track-label` pada kontainer seksi.
- Produces: Global tracking engine di `app.blade.php` menggunakan `IntersectionObserver`, `visibilitychange`, dan `sendBeacon`/`fetch`.

- [ ] **Step 1: Pasang atribut tracking pada seksi Beranda (`home.blade.php`)**
  - Hero Banner (`hero_banner`)
  - Kategori (`kategori`)
  - Our Collection (`our_collection`)
  - Program Affiliate (`affiliate_home`)
  - New Arrivals (`new_arrivals`)
- [ ] **Step 2: Pasang atribut tracking pada seksi Detail Produk (`products/show.blade.php`)**
  - Galeri & Foto (`product_gallery`)
  - Pilihan Varian & Beli (`product_variants`)
  - Deskripsi & Spesifikasi (`product_description`)
  - Ulasan Pembeli (`product_reviews`)
  - Produk Terkait (`related_products`)
- [ ] **Step 3: Pasang atribut tracking pada seksi Program Affiliate (`affiliate/index.blade.php`)**
  - Hero Affiliate (`affiliate_hero`)
  - Panduan 3 Langkah (`affiliate_steps`)
  - Simulasi Cuan (`affiliate_simulation`)
- [ ] **Step 4: Pasang atribut tracking pada seksi Keranjang & Checkout (`cart/index.blade.php` & `checkout/index.blade.php`)**
- [ ] **Step 5: Tambahkan skrip Dwell Tracker mandiri di `layouts/app.blade.php`**
- [ ] **Step 6: Verifikasi kompilasi Blade frontend**
  Run: `php artisan view:cache` lalu `php artisan view:clear`
- [ ] **Step 7: Commit Task 2 di repo frontend**

---

### Task 3: Agregasi Analitik Atensi Seksi di Admin Backend
**Files:**
- Modify: `backend-ecommerce-record/app/Http/Controllers/Web/AdminWebActivityLogController.php`

**Interfaces:**
- Consumes: Rekaman `activity_log` dengan `log_name = 'evaluasi_web'` dan `event = 'dwell'`.
- Produces: Key `'dwell_analytics'` dalam array `analytics()`:
  - `top_sections`: array ranking seksi teratas (nama seksi, halaman, total durasi terformat, frekuensi dilihat, rata-rata durasi per visit, % attention share).
  - `total_tracked_time`: total durasi akumulatif semua pengunjung.
  - `total_dwell_logs`: total frekuensi tayang seksi.

- [ ] **Step 1: Tambahkan `'evaluasi_web'` ke dalam `SHOP_LOG_NAMES` di `AdminWebActivityLogController`**
- [ ] **Step 2: Buat logika agregasi kalkulasi atensi seksi web dalam method `analytics()`**
- [ ] **Step 3: Linting `AdminWebActivityLogController.php`**
  Run: `php -l app/Http/Controllers/Web/AdminWebActivityLogController.php`
- [ ] **Step 4: Commit Task 3 di repo backend**

---

### Task 4: Desain UI Kartu Metrik Evaluasi Seksi Web & Detail Log di Admin
**Files:**
- Modify: `backend-ecommerce-record/resources/views/admin/activity-logs.blade.php`

**Interfaces:**
- Produces: Kartu Analitik Evaluasi Atensi Seksi Web dengan progress bar persentase atensi.
- Produces: Badge & format baris tabel untuk modul `evaluasi_web` (warna Amber/Oranye dengan ikon stopwatch).
- Produces: Tampilan modal detail aktivitas yang memuat rincian seksi, halaman, durasi, dan perangkat.

- [ ] **Step 1: Tambahkan Kartu Metrik Evaluasi Atensi Seksi Web di baris analitik atas**
- [ ] **Step 2: Tambahkan styling badge & ikon modul `evaluasi_web` pada dropdown filter dan tabel**
- [ ] **Step 3: Sesuaikan tampilan baris log agar menampilkan nama seksi dan format durasi dengan rapi**
- [ ] **Step 4: Perbarui modal detail Alpine.js untuk menampilkan detail informasi seksi web**
- [ ] **Step 5: Verifikasi kompilasi Blade backend**
  Run: `php artisan view:cache` lalu `php artisan view:clear`
- [ ] **Step 6: Commit Task 4 di repo backend**

---

### Task 5: Pengujian End-to-End, Verifikasi Data & Git Push
- [ ] **Step 1: Jalankan simulasi pengiriman data dwell time via cURL/script untuk memastikan pencatatan berhasil**
- [ ] **Step 2: Periksa apakah log masuk ke tabel `activity_log` dengan format JSON properti yang valid**
- [ ] **Step 3: Periksa apakah dashboard admin `activity-logs` menampilkan ranking dan kartu evaluasi atensi secara presisi**
- [ ] **Step 4: Git push `origin main` untuk kedua repositori (backend & frontend)**
