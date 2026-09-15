# Rancangan Desain: Pelacakan Durasi Atensi (Dwell Time) Seksi Web & Evaluasi Website di Admin

**Tanggal:** 2026-09-15  
**Status:** Disetujui (Approved)  
**Tujuan:** Merekam durasi waktu (*dwell time*) dan bagian/seksi mana saja yang dilihat oleh pengunjung (user login maupun tamu/guest) secara menyeluruh pada website e-commerce, serta menyajikannya dalam bentuk analitik evaluasi web di dashboard admin `admin.recordshoes.com` guna mengetahui bagian yang paling menarik perhatian pembeli.

---

## 1. Latar Belakang & Kebutuhan Bisnis

Untuk mengevaluasi efektivitas tata letak (UI/UX) dan materi promo toko online Record Shoes, pemilik toko memerlukan data kuantitatif yang dapat menjawab:
1. **Bagian Mana yang Paling Sering & Paling Lama Dilihat:** Apakah pengunjung benar-benar memperhatikan Hero Banner slider, atau langsung scroll ke Our Collection / New Arrivals?
2. **Efektivitas Program Affiliate:** Apakah banner Program Affiliate di beranda dan halaman edukasi affiliate dibaca dengan seksama oleh pengunjung?
3. **Atensi pada Halaman Produk:** Apakah pembeli membaca deskripsi & spesifikasi sepatu, atau hanya melihat foto galeri dan ulasan pembeli?
4. **Perbedaan Perilaku Tamu (*Guest*) vs Customer:** Bagaimana kebiasaan browsing pengunjung yang belum login dibandingkan customer terdaftar?

---

## 2. Cakupan Seksi Website yang Dilacak (*Coverage Matrix*)

Pelacakan mencakup seluruh seksi strategis website e-commerce:

### A. Halaman Utama (Beranda / Homepage)
1. `hero_banner` (Label: **Hero Banner**): Slider banner promo utama di bagian atas.
2. `kategori` (Label: **Kategori Produk**): Carousel pilihan kategori sepatu (Sepatu Sekolah, Cewek, Cowok, dll).
3. `our_collection` (Label: **Koleksi Unggulan / Our Collection**): Grid produk unggulan pilihan toko.
4. `affiliate_home` (Label: **Program Affiliate (Beranda)**): Banner promosi "Belanja Sekali, Cuan Berkali-kali".
5. `new_arrivals` (Label: **Produk Terbaru / New Arrivals**): Grid produk rilisan teranyar.

### B. Halaman Program Affiliate (`/affiliate`)
1. `affiliate_hero` (Label: **Hero Program Affiliate**): Header pengenalan program.
2. `affiliate_steps` (Label: **Panduan 3 Langkah Affiliate**): Bagian cara kerja (Belanja, Dapatkan Kode, Bagikan).
3. `affiliate_simulation` (Label: **Simulasi Cuan & Komisi**): Kartu kalkulasi keuntungan komisi R_Pay.

### C. Halaman Detail Produk (`/products/{slug}`)
1. `product_gallery` (Label: **Galeri & Foto Produk**): Foto utama dan thumbnail produk.
2. `product_variants` (Label: **Pilihan Varian & Ukuran**): Area seleksi ukuran sepatu, warna, dan tombol keranjang/beli.
3. `product_description` (Label: **Deskripsi & Spesifikasi Produk**): Rincian bahan, ukuran chart, dan panduan perawatan.
4. `product_reviews` (Label: **Ulasan & Bintang Pembeli**): Review, foto ulasan, dan komentar pelanggan.
5. `related_products` (Label: **Rekomendasi Produk Serupa**): Grid rekomendasi produk terkait di bagian bawah.

### D. Halaman Keranjang & Kasir (`/cart` & `/checkout`)
1. `cart_items` (Label: **Daftar Item Keranjang**): Daftar produk yang dimasukkan ke keranjang.
2. `cart_summary` (Label: **Ringkasan Belanja Keranjang**): Rincian subtotal dan tombol lanjut checkout.
3. `checkout_address` (Label: **Formulir Pengiriman & Alamat**): Pengisian nama penerima dan alamat.
4. `checkout_shipping` (Label: **Pemilihan Kurir & Ekspedisi**): Pilihan JNE, J&T, SiCepat, dll.
5. `checkout_payment` (Label: **Ringkasan Kasir & Pembayaran**): Rincian total akhir dan tombol bayar.

---

## 3. Arsitektur Teknis & Mekanisme Pengumpulan Data

### A. Mesin Pelacak Frontend (*Lightweight Client-side Observer*)
- **Teknologi:** `IntersectionObserver API` murni bawaan browser (tanpa dependensi eksternal, tanpa perlu `npm run build`).
- **Penandaan Elemen:** Atribut `data-track-section="{section_id}"` dan `data-track-label="{label}"` disematkan pada setiap kontainer seksi di Blade views.
- **Aturan Pengukuran Waktu (*Dwell Calculation*):**
  1. **Threshold Visibilitas:** Seksi dianggap "aktif dilihat" jika minimal **35-40%** area seksi berada di viewport layar.
  2. **Tab Visibility Guard:** Timer HANYA berjalan jika `document.visibilityState === 'visible'` dan jendela browser sedang aktif. Jika pengguna minimize atau berpindah tab, timer otomatis dijeda.
  3. **Filter Scroll Cepat (*Noise Filter*):** Kunjungan seksi di bawah **3 detik** diabaikan (mencegah pencatatan sampah saat pengunjung sekadar scroll cepat ke bawah).
- **Pengiriman Data (*Batch Beaconing*):**
  - Akumulasi detik disimpan sementara di memori client per seksi.
  - Data dikirim ke server:
    1. Setiap **45 detik** (interval terjadwal, hanya jika ada akumulasi waktu baru).
    2. Saat pengguna berpindah halaman / menutup tab (`beforeunload` dan `visibilitychange` hidden) menggunakan `navigator.sendBeacon` atau `fetch(..., { keepalive: true })`.

### B. Endpoint Penerima di Frontend Store
- **Route:** `POST /track/section-dwell`
- **Controller:** `App\Http\Controllers\TrackingController::recordSectionDwell(Request $request)`
- **Keamanan & Validasi:**
  - Validasi payload: `section_id` (string max 60), `section_label` (string max 100), `duration_seconds` (integer min 3 max 3600), `page_url` (string max 255), `page_name` (string max 100).
  - Mengabaikan durasi yang tidak masuk akal (> 1 jam per satu kali kirim).
- **Pencatatan ke Database:**
  - Menggunakan helper `CatatAktivitas::tulis(...)`:
    - `log_name`: `'evaluasi_web'`
    - `event`: `'dwell'`
    - `description`: `Pengunjung melihat seksi "{section_label}" di {page_name} selama {format_menit_detik}`
    - `causer_id`: ID Customer jika login, atau `null` jika Tamu (*Guest*).
    - `properties`:
      - `section_id`: kode unik seksi
      - `section_label`: label nama seksi
      - `page_url`: URL halaman toko
      - `page_name`: nama halaman (Beranda, Detail Produk, Affiliate, dll)
      - `duration_seconds`: jumlah detik
      - `duration_formatted`: string mudah dibaca (misal: "1m 24d" atau "45 detik")
      - `ip`, `device` (Mobile/Desktop), `platform`, `browser`, `user_agent` (otomatis dari `DeviceDetector`).

---

## 4. Penyajian Data & Analitik di Backend (`admin.recordshoes.com`)

### A. Kontroller Backend (`AdminWebActivityLogController`)
1. **Dukungan Modul Baru:**
   - Menambahkan `'evaluasi_web'` ke dalam `SHOP_LOG_NAMES` agar otomatis terkelompokkan ke dalam Tab **"User & Tamu"** dan **"Semua"**.
2. **Kalkulasi Metrik Analitik Atensi (`dwell_analytics`):**
   - Mengagregasi data log `log_name = 'evaluasi_web'` dan `event = 'dwell'` dalam 7 hari terakhir:
     - **Total Waktu Dilihat (Total Dwell Time):** Penjumlahan seluruh `duration_seconds` per seksi.
     - **Frekuensi Dilihat (Total Views / Impressions):** Jumlah kali seksi dilihat >= 3 detik.
     - **Rata-rata Waktu Dilihat (Average Dwell Time):** Total durasi / Frekuensi dilihat.
     - **Pangsa Atensi (% Attention Share):** Persentase waktu yang dihabiskan pengunjung di seksi tersebut dibandingkan total seluruh seksi.
     - **Urutan Ranking:** Mengurutkan 5 seksi paling menyita perhatian pengunjung (Top Engaged Sections).

### B. Antarmuka UI (`resources/views/admin/activity-logs.blade.php`)
1. **Kartu Metrik Evaluasi Seksi Web (Di Atas Tabel Log):**
   - Menambahkan kartu analitik khusus:
     - Judul: **Evaluasi Atensi Seksi Website** (Top Engaged Sections).
     - Menampilkan daftar 5 seksi teratas lengkap dengan:
       - Nama seksi & halaman.
       - Bar persentase atensi berwarna amber/oranye gradasi.
       - Rata-rata durasi waktu stay (contoh: *2m 14d per visit*).
       - Total frekuensi tayang (contoh: *1.480 kali dilihat*).
2. **Badge & Ikon Modul:**
   - Badge modul `evaluasi_web`: Warna Oranye / Amber (`bg-amber-50 text-amber-700 border-amber-200`) dengan ikon stopwatch/mata (`fa-solid fa-stopwatch`).
3. **Format Baris Log di Tabel:**
   - Subjek: Seksi Web (contoh: `Seksi #our_collection`).
   - Deskripsi: *"Tamu melihat seksi: Koleksi Unggulan (Our Collection) di Beranda selama 1m 20d"*.
   - Pelaku: Badge "Tamu (Guest)" atau Nama Customer.
   - Kolom Perangkat: Mobile / Desktop / Tablet lengkap dengan Browser & IP.
4. **Modal Detail Aktivitas:**
   - Menampilkan kartu informasi seksi web:
     - Nama Seksi & Kategori Halaman.
     - Durasi Stay (detik & format menit).
     - URL Lengkap Halaman Toko.

---

## 5. Rencana Verifikasi & Uji Kualitas

1. **Uji Validasi Sintaks PHP:**
   - `php -l` pada controller baru dan controller yang dimodifikasi di backend & frontend.
2. **Uji Kompilasi Blade:**
   - `php artisan view:cache` di backend memastikan tidak ada galat parsing Blade.
3. **Uji Simulasi Browser:**
   - Buka beranda di frontend, scroll ke seksi Hero Banner, Our Collection, Program Affiliate, dan New Arrivals.
   - Tetap di masing-masing seksi selama 10-15 detik.
   - Pindah ke halaman detail produk dan affiliate.
   - Periksa apakah data terkirim via endpoint `/track/dwell` dan tersimpan rapi di tabel `activity_log`.
4. **Uji Dashboard Admin:**
   - Buka `/admin/activity-logs` di backend.
   - Pastikan Kartu Evaluasi Atensi Seksi Web menghitung total durasi, rata-rata, dan pangsa atensi dengan akurat.
   - Filter tabel dengan modul `evaluasi_web` dan pastikan data log tamu/customer tampil sempurna.
