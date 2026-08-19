# Petunjuk & Aturan Workspace (Koding E-Commerce)

Dokumen ini digunakan oleh AI Asisten untuk mengingat detail penting proyek ini tanpa perlu menganalisis ulang dari awal di masa mendatang.

## ⚙️ Lingkungan & Command PHP
- **Frontend (`frontend-ecommerce-record`):**
  - Menggunakan dependensi Composer yang mewajibkan **PHP >= 8.4.1**.
  - CLI global Windows berjalan di PHP 8.2.12.
  - **PENTING:** Selalu gunakan prefix `herd` sebelum menjalankan command PHP di folder frontend (contoh: `herd php artisan cache:clear`, `herd php artisan tinker`).
- **Backend (`backend-ecommerce-record`):**
  - Berjalan di PHP 8.2.12. Command artisan biasa dapat dijalankan langsung (`php artisan ...`).

## 🗄️ Database & Koneksi
- Koneksi menggunakan MySQL lokal di `127.0.0.1:3306` dengan nama database `ecommerce_record`.
- Username: `root`, Password: `` (kosong).
- **Penting:** Frontend dan Backend terhubung ke database yang sama ini secara langsung.

## 🏷️ Logika Sistem Diskon Produk & Varian
- Tabel `discounts` mendukung diskon tingkat produk maupun varian secara spesifik:
  - Kolom `product_variant_id` bernilai `NULL` = Diskon berlaku untuk produk utama (seluruh produk).
  - Kolom `product_variant_id` terisi ID varian = Diskon khusus untuk varian tersebut saja (mengabaikan/meng-override diskon utama produk).
- **Logika Tanggal (Starts & Ends At):**
  - Jika tanggal mulai/selesai dikosongkan (`null`), diskon berjalan selamanya.
  - Untuk menghindari kedaluwarsa prematur karena jam, `starts_at` harus disimpan dengan `startOfDay()` (00:00:00) dan `ends_at` dengan `endOfDay()` (23:59:59).
- **Eager Loading Relasi di Frontend:**
  - `Product::activeDiscount()` difilter khusus untuk `product_variant_id IS NULL`.
  - `ProductVariant` memiliki `activeDiscount()` khusus varian. Logika harga final varian (`effective_price`) memeriksa diskon varian terlebih dahulu, baru fallback ke diskon produk utama.
  - Selalu eager load relasi diskon saat query keranjang, detail produk, atau checkout: `with(['product.activeDiscount', 'productVariant.activeDiscount'])`.

## 🎨 Pola Pengembangan & UI
- Pengaturan diskon admin di `discounts.blade.php` dikelola menggunakan **Alpine.js**.
- Deklarasi `x-data` Alpine pada tabel diskon diletakkan di tag `<tbody>` per produk (bukan `<tr>`), agar baris utama dan baris-baris varian (`template x-for`) berada dalam satu scope data yang sama.
- Tombol aksi simpan per-baris dihapus, diganti dengan **Sticky Bottom Action Bar** melayang di bawah layar yang memproses penyimpanan massal data kustom varian & produk secara interaktif.
