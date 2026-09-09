# Rancangan Desain: Pelacakan Customer Journey, Pencatatan Detail Produk, dan Statistik Login Backend

**Tanggal:** 2026-09-09  
**Status:** Disetujui (Approved)  
**Tujuan:** Memberikan visibilitas menyeluruh terhadap aktivitas customer dan tamu (*guest*), merekam detail produk yang dilihat, serta menyajikan statistik login dan proporsi perangkat di backend admin untuk keperluan evaluasi website toko online.

---

## 1. Latar Belakang & Kebutuhan
Pemilik toko online membutuhkan sistem analitik dan audit trail yang mampu menjawab:
1. **Berapa banyak yang login:** Jumlah login customer dan admin (hari ini, 7 hari, dan total), serta perbandingannya.
2. **Gerakan User / Tamu (Customer Journey):** Setiap interaksi penting pengunjung tercatat di log aktivitas (misalnya membuka detail produk tertentu, melakukan pencarian, menambahkan produk ke keranjang, dan membuka checkout).
3. **Detail Produk yang Dilihat:** Mengetahui secara spesifik produk apa yang dilihat oleh pengunjung (nama produk, kategori, harga, stok).
4. **Informasi Perangkat & IP:** Mengetahui apakah customer mengakses melalui HP (Mobile), Laptop/PC (Desktop), atau Tablet, lengkap dengan IP address dan browser untuk mengevaluasi responsivitas website toko.

---

## 2. Arsitektur & Titik Pelacakan (Frontend Store)

### A. Pelacakan Produk (`produk`)
- **Lokasi:** `ProductController::show(Product $product)`
- **Aksi:** `view`
- **Deskripsi:** `Melihat produk: {nama_produk} (Rp {harga})`
- **Metadata Properti:**
  - `product_id`: ID produk
  - `product_name`: Nama produk
  - `product_slug`: Slug produk
  - `category`: Nama kategori
  - `price`: Harga produk saat dilihat
  - `stock`: Sisa stok
  - `total_variants`: Jumlah varian
  - `ip`, `device` (Mobile/Desktop/Tablet), `platform`, `browser`, `user_agent`
- **Session Guard (Anti-Spam):**
  - Menggunakan `session()->has('viewed_product_' . $product->id)` dengan jeda 5 menit agar refresh berulang oleh customer yang sama tidak membanjiri database.

### B. Pelacakan Pencarian Produk (`pencarian`)
- **Lokasi:** `ProductController::index(Request $request)` saat `$request->filled('search')`
- **Aksi:** `search`
- **Deskripsi:** `Mencari produk dengan kata kunci "{search}" ({total_hasil} produk ditemukan)`
- **Metadata Properti:**
  - `keyword`: Kata kunci pencarian
  - `results_count`: Jumlah produk yang cocok ditemukan
  - `category_filter`: Kategori yang dipilih jika ada
  - `ip`, `device`, `browser`, `user_agent`

### C. Pelacakan Keranjang Belanja (`keranjang`)
- **Tambah ke Keranjang (`CartController::store`):**
  - **Aksi:** `add_to_cart`
  - **Deskripsi:** `Menambahkan produk ke keranjang: {nama_produk} (Qty: {qty})`
  - **Metadata:** ID produk, nama produk, varian, kuantitas, harga satuan, subtotal.
- **Hapus dari Keranjang (`CartController::destroy`):**
  - **Aksi:** `remove_from_cart`
  - **Deskripsi:** `Menghapus produk dari keranjang: {nama_produk}`

### D. Pelacakan Alur Checkout (`checkout`)
- **Buka Checkout (`CheckoutController::index`):**
  - **Aksi:** `checkout_view`
  - **Deskripsi:** `Membuka halaman kasir/checkout ({jumlah_item} item, Total Rp {grand_total})`
- **Pilih Kurir / Hitung Ongkir (`CheckoutController::calculateShippingCost`):**
  - **Aksi:** `select_courier`
  - **Deskripsi:** `Memilih opsi pengiriman: {courier} ({service}) - Rp {cost}`

### E. Pelacakan Autentikasi Customer & Admin (`auth`)
- **Customer Login (`AuthenticatedSessionController::store`):**
  - **Aksi:** `login`
  - **Deskripsi:** `Customer masuk (login) ke akun`
- **Customer Register (`RegisteredUserController::store`):**
  - **Aksi:** `register`
  - **Deskripsi:** `Customer baru mendaftar akun: {name} ({email})`
- **Customer Logout (`AuthenticatedSessionController::destroy`):**
  - **Aksi:** `logout`
  - **Deskripsi:** `Customer keluar (logout) dari akun`
- **Admin Login (`AdminWebAuthController::login`):**
  - **Aksi:** `login`
  - **Deskripsi:** `Administrator masuk (login) ke dashboard admin`
- **Admin Logout (`AdminWebAuthController::logout`):**
  - **Aksi:** `logout`
  - **Deskripsi:** `Administrator keluar (logout) dari dashboard admin`

---

## 3. Penyajian Data & Analitik di Backend (`activity-logs`)

### A. Kontroller Backend (`AdminWebActivityLogController`)
Menghitung metrik analitik:
1. **Statistik Login:**
   - Total Login (Semua, Hari Ini, 7 Hari).
   - Pembagian Login: Admin Login vs Customer Login.
2. **Proporsi Perangkat (Mobile vs Desktop):**
   - Persentase & jumlah total akses dari Mobile vs Desktop vs Tablet.
3. **Produk Terpopuler (Top 3 Viewed Products dalam 7 Hari):**
   - Diambil dari agregasi log aktivitas ber-log_name `produk` & event `view`.
4. **Pencarian Terpopuler (Top 3 Search Keywords):**
   - Diambil dari log aktivitas `pencarian`.
5. **Dukungan Filter Baru:**
   - Menambahkan `auth`, `produk`, `pencarian`, `keranjang`, `checkout` ke daftar filter Modul di UI.

### B. Antarmuka UI (`resources/views/admin/activity-logs.blade.php`)
1. **Row Kartu Statistik Evaluasi:**
   - **Kartu 1: Total Login & Pengguna Aktif** (Badge counter hari ini & minggu ini, pill Admin vs Customer).
   - **Kartu 2: Analisis Perangkat** (Progress bar visual % Mobile vs % Desktop).
   - **Kartu 3: Produk Paling Sering Dilihat** (Menampilkan 3 produk teratas beserta jumlah dilihat).
   - **Kartu 4: Kata Kunci Pencarian Teratas** (Pill tags kata kunci populer).
2. **Badge Modul & Aksi:**
   - `auth`: Icon kunci, warna Indigo / Biru Tua.
   - `produk`: Icon mata/produk, warna Teal / Cyan.
   - `pencarian`: Icon kaca pembesar, warna Slate.
   - `keranjang`: Icon keranjang belanja, warna Amber.
   - `checkout`: Icon kartu kredit, warna Ungu.
3. **Modal Detail Aktivitas:**
   - Menampilkan card khusus "Detail Produk" jika log bertipe produk (nama produk, kategori, harga, sisa stok).
   - Menampilkan card "Informasi Perangkat" (Tipe Perangkat, Platform/OS, Browser, IP, User Agent).

---

## 4. Rencana Verifikasi
1. **PHP Syntax & Linting:** `php -l` pada semua controller dan view yang diubah di kedua repositori.
2. **Blade Compile Verification:** `php artisan view:cache` di backend memastikan tidak ada galat sintaks Blade.
3. **Fungsionalitas Pelacakan:**
   - Buka halaman produk di frontend → Cek apakah tercatat di `activity_log` dengan rincian nama produk, kategori, harga, stok, IP, dan tipe perangkat.
   - Lakukan pencarian kata kunci → Cek apakah kata kunci dan jumlah hasil tercatat.
   - Tambah produk ke keranjang & buka checkout → Cek apakah tercatat.
   - Login customer & login admin → Cek apakah tercatat dan counter login di dasbor log aktivitas bertambah secara presisi.
