# Rencana Implementasi: Fitur Perbandingan Atensi Seksi Website Hari ke Hari (Day-over-Day Comparison)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membangun fitur perbandingan atensi seksi website (*Day-over-Day dwell time comparison*) pada log aktivitas admin Seller Center, menyajikan matriks harian, persentase pertumbuhan ($\Delta\%$), kartu ringkasan cepat, dan perbandingan otomatis H-1 saat filter tanggal tunggal dipilih.

**Architecture:** 
1. Controller mengagregasi data log aktivitas `evaluasi_web` per tanggal dan per seksi, menghitung persentase perubahan harian (DoD $\Delta\%$), serta menangani baseline H-1 otomatis.
2. Blade view menyediakan sub-tab switch modern (*Ringkasan Seksi* vs *Perbandingan Harian*), 3 kartu ringkasan cepat (*Top Performer*, *Top Gainer*, *Total Toko DoD*), dan tabel matriks harian yang responsif.

**Tech Stack:** PHP 8.2 (Laravel 11), Blade, Tailwind CSS, Alpine.js, Spatie ActivityLog.

## Global Constraints
- PHP CLI Backend: PHP 8.2.12 (`php artisan ...`).
- Seluruh query dan agregasi harus tahan terhadap kasus nilai 0 (mencegah *division by zero*).
- Desain UI wajib bersih, presisi, anti-slop, dan selaras dengan tema Tailwind yang sudah ada di halaman Log Aktivitas.

---

### Task 1: Backend Agregasi & Komparasi Harian di Controller

**Files:**
- Modify: `app/Http/Controllers/Web/AdminWebActivityLogController.php`

**Interfaces:**
- Produces: `$analytics['dwell_comparison']` array:
  - `dates`: array tanggal `['2026-09-18', ..., '2026-09-22']` dengan metadata format label (`Kam, 18 Sep`, `is_today`, dsb.)
  - `sections`: array seksi dengan data per tanggal:
    - `key`, `label`, `icon`
    - `days`: `[date_str => ['seconds', 'formatted', 'views', 'delta_pct', 'trend']]`
    - `total_period_seconds`, `avg_period_seconds`
  - `daily_totals`: `[date_str => ['total_seconds', 'formatted', 'delta_pct', 'trend']]`
  - `top_gainer`: `['label', 'delta_pct', 'seconds_diff']` atau `null`
  - `top_performer`: `['label', 'seconds', 'formatted']` atau `null`
  - `single_date_mode`: boolean (true jika user filter tepat 1 tanggal, menyandingkan H-1 vs H)

- [ ] **Step 1: Implementasikan method perhitungan rentang tanggal komparasi**
  - Tentukan daftar tanggal yang akan dikomparasikan.
  - Jika filter kustom `from == to` atau hanya `from` / `to`: buat 2 tanggal: `$baselineDate = date - 1 day` dan `$currentDate = date`.
  - Jika rentang multi-hari: buat daftar tanggal dari `from` s/d `to`, ditambah `$from - 1 day` sebagai baseline pembanding hari pertama.
  - Jika default `7d`: ambil 7 hari terakhir s/d hari ini, plus 1 hari sebelumnya sebagai baseline.
- [ ] **Step 2: Query dan Agregasi Log Dwell Time Harian**
  - Ambil records `Activity::where('log_name', 'evaluasi_web')->where('event', 'dwell')` dalam rentang tanggal komparasi.
  - Kelompokkan durasi (`seconds`) per `date` dan per `section`.
- [ ] **Step 3: Kalkulasi DoD Delta % dan Metrik Ringkasan**
  - Hitung persentase kenaikan/penurunan ($\Delta\%$) untuk tiap seksi dibanding hari sebelumnya.
  - Hitung total atensi seluruh web per hari dan persentase perubahan total web.
  - Cari seksi *Top Gainer* dan *Top Performer*.
- [ ] **Step 4: Sertakan `dwell_comparison` ke dalam return `$analytics`**
  - Masukkan ke array return di method `buildAnalytics()`.
- [ ] **Step 5: Verifikasi via PHP Tinker / Command**
  - Jalankan script verifikasi untuk memastikan controller mengembalikan struktur array `dwell_comparison` dengan benar tanpa error.

---

### Task 2: Implementasi Antarmuka UI Matriks & Kartu Ringkasan di Blade

**Files:**
- Modify: `resources/views/admin/activity-logs.blade.php`

**Interfaces:**
- Consumes: `$analytics['dwell_comparison']`, `$analytics['dwell_sections']`

- [ ] **Step 1: Tambahkan Sub-tab Switcher di Header Kartu Atensi**
  - Inisialisasi Alpine.js state: `dwellSubTab: 'summary'` (dengan opsi `'summary'` dan `'comparison'`).
  - Tambahkan tombol pill toggle di samping filter tanggal.
- [ ] **Step 2: Buat 3 Kartu Ringkasan Cepat (*Highlights*)**
  - Kartu 1: Seksi Terpopuler Hari Terakhir.
  - Kartu 2: Lonjakan Tertinggi (*Top Gainer* $\Delta\%$).
  - Kartu 3: Total Atensi Toko Hari Terakhir vs Kemarin.
- [ ] **Step 3: Bangun Tabel Matriks Harian (*Day-by-Day Matrix Table*)**
  - Header tabel dengan kolom seksi dan kolom-kolom tanggal (`Sen, 21 Sep`, `Sel, 22 Sep`).
  - Kolom seksi di sebelah kiri dengan ikon dan label.
  - Sel harian dengan durasi tebal dan badge $\Delta\%$ (hijau ↗️, merah ↘️, abu-abu).
  - Kolom total/rata-rata periode di sisi kanan.
  - Baris footer total seluruh toko per hari beserta tren perubahannya.
- [ ] **Step 4: Bersihkan View Cache**
  - Jalankan `php artisan view:clear` di backend.

---

### Task 3: Verifikasi Fungsional & Kasus Ekstrem (*Edge Cases*)

- [ ] **Step 1: Uji filter tanggal tunggal**
  - Akses `activity-logs?dwell_from=2026-09-20&dwell_to=2026-09-20`.
  - Pastikan kolom menampilkan Tanggal 19 Sep dan 20 Sep dengan selisih yang akurat.
- [ ] **Step 2: Uji filter rentang tanggal multi-hari**
  - Akses `activity-logs?dwell_period=7d` dan kustom range.
  - Pastikan semua kolom tanggal berurutan rapi.
- [ ] **Step 3: Uji penanganan data kosong / 0 detik**
  - Pastikan tidak ada warning PHP *Division by zero* atau NaN di UI.
