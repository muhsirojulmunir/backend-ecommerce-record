# Desain Teknis: Pembatasan Voucher Promo "RECORD" (1x Akun & 1x Device) + Log Pemakaian

## 1. Ringkasan Fitur
Fitur ini bertujuan untuk mengizinkan pembuatan voucher promosi massal (khususnya voucher dengan kode **"RECORD"**) yang dapat digunakan oleh banyak pelanggan, namun memiliki proteksi anti-kecurangan ganda (*anti-fraud*):
1. **1 Akun hanya dapat menggunakan 1 kali.**
2. **1 Perangkat (Device) fisik hanya dapat menggunakan 1 kali.**

Fitur ini dirancang khusus untuk voucher bertipe promo tanpa mengganggu cara kerja voucher sekali pakai (*single-use*) seperti kupon hasil Game Stopwatch maupun voucher batch cetak fisik. Selain itu, admin dapat memantau secara langsung riwayat dan statistik pemakaian voucher ini (siapa penggunanya, perangkat apa yang digunakan, nomor order, dan waktu transaksi).

---

## 2. Perubahan Database

### 2.1 Modifikasi Tabel `vouchers`
Menambahkan kolom pendukung tipe promo dan batasan kuota pada tabel `vouchers`:
* `type`: `enum('single', 'promo')` default `'single'` (membedakan voucher kupon unik/game vs voucher kode promo bersama seperti "RECORD").
* `total_quota`: `integer unsigned` nullable (opsional kuota total pemakaian, misal 100).
* `used_count`: `integer unsigned` default `0` (jumlah berapa kali voucher ini sudah sukses digunakan).
* `limit_per_user`: `smallint unsigned` default `1` (batas pemakaian per akun).
* `limit_per_device`: `smallint unsigned` default `1` (batas pemakaian per perangkat fisik).

### 2.2 Tabel Baru: `voucher_usages`
Tabel pencatat (*audit log*) pemakaian voucher secara permanen saat pesanan berhasil dibuat (checkout sukses):
* `id`: `bigint unsigned auto-increment primary key`
* `voucher_id`: `foreignId constrained to vouchers` (onDelete cascade)
* `user_id`: `foreignId constrained to users` (onDelete cascade)
* `order_id`: `foreignId constrained to orders` nullable (onDelete set null)
* `device_id`: `string(64)` (hash unik fingerprint perangkat)
* `device_label`: `string(100)` nullable (keterangan perangkat, contoh: "Windows 11 · Chrome", diambil via `DeviceDetector`)
* `ip_address`: `string(45)` nullable
* `user_agent`: `text` nullable
* `discount_amount`: `unsignedBigInteger` (nominal potongan)
* `created_at` / `updated_at`: `timestamp`
* **Index**:
  * `index(['voucher_id', 'device_id'])` untuk cek cepat per perangkat
  * `index(['voucher_id', 'user_id'])` untuk cek cepat per akun

---

## 3. Identifikasi Perangkat (Device Identification)

### 3.1 Sisi Frontend Web Store
* Skrip Javascript ringan (akan ditempatkan pada layout utama / checkout frontend):
  1. Memeriksa apakah `record_device_id` sudah ada di `localStorage` atau `Cookie`.
  2. Jika belum ada, buat `device_id` berbasis kombinasi karakteristik perangkat (*Canvas rendering hash*, *Screen dimensions & color depth*, *Timezone offset*, *Platform/UserAgent*) dipadukan dengan UUID v4.
  3. Simpan token `record_device_id` di `localStorage` dan `Cookie` (dengan masa aktif 2 tahun).
* Pada saat:
  * Pengecekan voucher di keranjang/checkout (`/api/customer/voucher/check`), kirim header `X-Device-Id` atau payload `device_id`.
  * Submit checkout (`OrderService@checkout`), kirim field `device_id`.

---

## 4. Alur Validasi & Eksekusi Backend

### 4.1 Validasi Kode Voucher (`CustomerVoucherController@check`)
Saat customer mengecek kode voucher:
1. Jika voucher bertipe `promo` (atau kodenya `RECORD`):
   * Cek kedaluwarsa (`expires_at`).
   * Cek kuota: jika `total_quota` diset dan `used_count >= total_quota`, tolak: *"Kuota voucher ini sudah habis."*
   * Cek akun (jika user login): apakah `user_id` sudah ada di `voucher_usages` untuk voucher ini? Jika ya, tolak: *"Akun Anda sudah pernah menggunakan voucher ini."*
   * Cek device: apakah `device_id` sudah ada di `voucher_usages` untuk voucher ini? Jika ya, tolak: *"Perangkat ini sudah pernah menggunakan voucher RECORD (Maksimal 1x pemakaian per perangkat)."*
2. Jika lolos, kirim respons valid berserta besaran potongan.

### 4.2 Eksekusi Saat Checkout (`OrderService@checkout`)
1. Di dalam transaksi database checkout:
   * Validasi ulang syarat voucher (kuota, akun, dan device_id).
   * Kurangi potongan harga pada pesanan.
   * Jika voucher tipe `promo`:
     * Buat catatan baru di tabel `voucher_usages` (`voucher_id`, `user_id`, `order_id`, `device_id`, `device_label`, `ip_address`, `discount_amount`).
     * Naikkan `used_count` sebesar 1. (Tidak mengubah `is_used` menjadi true, agar user lain tetap bisa menggunakan).
   * Jika voucher tipe `single`:
     * Jalankan flow lama: `$voucher->markAsUsed($userId, $order->id)`.

---

## 5. Antarmuka Admin "Kelola Voucher"

### 5.1 Form Pembuatan Voucher Promo
* Menambahkan pilihan tipe pada form buat voucher di `resources/views/admin/vouchers.blade.php`:
  * **Voucher Acak / Single-Use** (alur bawaan untuk cetak/game)
  * **Voucher Promo Massal (RECORD)**:
    * Input kode custom (default: `RECORD`).
    * Input nominal diskon (Rp).
    * Input batas kuota (opsional).
    * Tanggal kadaluarsa (opsional).
    * Kategori produk (opsional/universal).
    * Indikator proteksi otomatis: "Kunci 1x per Akun & 1x per Perangkat".

### 5.2 Tabel & Riwayat Pemakaian
* Menampilkan badge **"Promo Massal"** pada kolom tipe/status untuk voucher bertipe promo.
* Menampilkan ringkasan pemakaian: `used_count / total_quota`.
* Menambahkan tombol **"Lihat Riwayat"** (`fa-solid fa-list-check`) pada baris voucher promo.
* Modal Riwayat Pemakaian menampilkan tabel:
  * No. Order
  * Nama & Email User
  * Keterangan Perangkat & Icon (Desktop, Mobile, Brand/Browser via `DeviceDetector`)
  * Alamat IP
  * Potongan
  * Waktu Transaksi

---

## 6. Rencana Pengujian
1. **Tes Kuota & Akun**: Gunakan akun A dengan voucher RECORD, pastikan checkout berhasil. Coba gunakan lagi dengan akun A $\rightarrow$ harus ditolak.
2. **Tes Multi-Account di Perangkat yang Sama**: Logout akun A, login dengan akun baru (Akun B) di browser yang sama $\rightarrow$ coba gunakan voucher RECORD $\rightarrow$ harus ditolak karena `device_id` sudah pernah tercatat.
3. **Tes Perangkat Berbeda**: Buka di perangkat/browser yang benar-benar berbeda (atau mode browser berbeda dengan token berbeda) dengan Akun C $\rightarrow$ harus berhasil.
4. **Tes Voucher Single/Game**: Pastikan voucher game stopwatch atau voucher batch acak tetap bekerja normal 100% tanpa ada regresi.
