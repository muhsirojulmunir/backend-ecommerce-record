# Spesifikasi Desain: Sistem Pembayaran Manual Transfer BCA & Upload Bukti Pembayaran

**Tanggal:** 07 September 2026  
**Status:** Disetujui (Approved)  
**Sifat Perubahan:** Murni Penambahan (Pure Additive) — Tanpa Mengubah/Mengganggu Logika Midtrans atau R_Pay yang Sudah Berjalan.

---

## 1. Latar Belakang & Tujuan
Saat ini toko menggunakan Midtrans sebagai payment gateway utama serta R_Pay untuk dompet internal. Namun, Midtrans terkadang mengalami keterlambatan antrean proses atau pembeli ingin opsi transfer bank langsung tanpa biaya/antrean gateway.

Tujuan penambahan ini adalah menyediakan opsi pembayaran **Transfer Manual BCA**:
- **Bank:** BCA
- **Nomor Rekening:** `1000028122`
- **Atas Nama:** `Lily Minawati Prajogo`
- **Fitur Unggah Bukti Transfer:** Pembeli wajib/dapat mengunggah foto struk/bukti transfer bank.
- **Verifikasi Admin:** Uang wajib diverifikasi terlebih dahulu oleh admin melalui mutasi rekening BCA. Setelah admin mengonfirmasi dana masuk, pesanan baru berubah menjadi lunas (`paid`) dan diproses (`processing`), lalu siap dikirim kurir.

---

## 2. Prinsip Non-Interference (Pure Additive)
1. **Midtrans Tetap Utuh:**
   - Semua saluran Midtrans (`QRIS`, `BCA` VA, `BNI`, `BRI`, `Mandiri`, `Indomaret`, `Alfamart`) tetap berfungsi seperti biasa.
   - Fungsi `createSnapToken`, `paymentStatus`, dan webhook `midtransCallback` tidak dimodifikasi logikanya untuk transaksi Midtrans.
   - Pengecekan Midtrans otomatis (seperti `syncMidtransPaymentStatus` di controller admin) secara eksplisit **mengecualikan** pesanan transfer manual (`MANUAL_BCA`), sehingga Midtrans tidak akan menerima kueri status untuk pesanan manual (mencegah error 404 / API failure).
2. **R_Pay & COD Tetap Utuh:**
   - Logika pembayaran instan R_Pay dan COD tidak terpengaruh.

---

## 3. Struktur Data & Database
Menambahkan kolom baru pada tabel `orders` melalui migrasi database:
- `payment_proof` (`varchar(255)`, nullable): Menyimpan path berkas gambar bukti pembayaran yang diunggah pembeli ke storage (`storage/app/public/payment_proofs/...`).
- `payment_proof_uploaded_at` (`timestamp`, nullable): Waktu pembeli mengunggah bukti pembayaran.
- `payment_rejection_note` (`text`, nullable): Catatan dari admin jika bukti pembayaran ditolak/minta diunggah ulang (misal: "Nominal tidak sesuai", "Dana belum tampak di mutasi").

Model `Order` di Backend dan Frontend diperbarui untuk menambahkan ketiga kolom tersebut ke dalam `$fillable` dan `$casts`.

---

## 4. Alur Kerja Pengguna (User Flow)

### A. Alur Checkout (Frontend)
1. Di halaman Checkout (`/checkout`):
   - Modal pilihan pembayaran menampilkan 2 kelompok utama:
     - **Transfer Bank Manual (BCA):** Rekening BCA `1000028122` a.n Lily Minawati Prajogo (dengan keterangan: "Verifikasi Manual oleh Admin via Bukti Transfer"). Kode pembayaran: `MANUAL_BCA`.
     - **Pembayaran Otomatis (Midtrans):** QRIS, BCA Virtual Account, BNI, BRI, Mandiri, Alfamart, Indomaret (Verifikasi Otomatis 24 Jam).
     - *(R_Pay tetap tampil jika pengguna memiliki saldo cukup).*
2. Saat pembeli memilih `MANUAL_BCA` dan menekan "Buat Pesanan":
   - Order dibuat dengan `payment_method = 'MANUAL_BCA'`, `payment_status = 'unpaid'`, `status = 'pending'`.
   - Pembeli diarahkan ke halaman pembayaran: `/checkout/payment/{orderNumber}`.

### B. Halaman Instruksi Pembayaran & Upload Bukti (`/checkout/payment/{orderNumber}`)
1. Jika `order->payment_method === 'MANUAL_BCA'`:
   - Sistem **tidak** meminta Snap Token ke Midtrans.
   - Tampil kartu instruksi Transfer Bank Manual:
     - Logo / Badge BCA.
     - Nomor Rekening: `1000028122` dilengkapi tombol **"Salin No. Rekening"**.
     - Atas Nama: `Lily Minawati Prajogo`.
     - Total Tagihan yang harus ditransfer (format rupiah tebal).
   - Tampil bagian **"Unggah Bukti Transfer"**:
     - Jika bukti belum diunggah: Form drag-and-drop / input file gambar (JPG, PNG, JPEG, WEBP, maks 5MB) dengan tombol "Unggah Bukti Pembayaran".
     - Jika bukti sudah diunggah:
       - Tampil pratinjau foto struk bukti transfer.
       - Status lencana: **"⏳ Menunggu Verifikasi Admin"** (`payment_status: pending_verification`).
       - Catatan estimasi verifikasi.
       - Tombol **"Unggah Ulang Bukti"** (jika pembeli keliru mengunggah).
     - Jika bukti ditolak admin:
       - Tampil kotak peringatan merah dengan alasan penolakan dari admin (`payment_rejection_note`).
       - Form unggah ulang aktif kembali.
2. Halaman Detail Pesanan Pembeli (`/orders/{orderNumber}`):
   - Jika pesanan `MANUAL_BCA` belum lunas, pembeli juga dapat melihat info transfer dan mengunggah/mengganti bukti pembayaran langsung dari halaman detail pesanan ini.

---

## 5. Alur Kerja Admin (Backend)

### A. Daftar Pesanan (`/admin/orders`)
1. Pesanan manual yang sudah mengunggah bukti (`payment_status: pending_verification`) memiliki lencana pembeda: **"Bukti Menunggu Verifikasi"**.
2. Tab "Belum Bayar" memudahkan admin memfilter pesanan yang siap dicek mutasinya.

### B. Detail Pesanan Admin (`/admin/orders/{id}`)
1. Terdapat kartu khusus **"Verifikasi Pembayaran Manual"**:
   - Informasi bank tujuan: BCA `1000028122` a.n Lily Minawati Prajogo.
   - Total nominal pesanan.
   - Foto bukti transfer yang dapat diklik untuk diperbesar (pratinjau / tab baru).
   - Waktu unggah bukti.
2. Tombol Aksi Admin:
   - **Tombol Hijau: "Konfirmasi Dana Masuk (Lunas)"**
     - Memperbarui `payment_status = 'paid'`.
     - Memperbarui `status = 'processing'`.
     - Memicu penerbitan nomor invoice resmi otomatis.
     - Mengirimkan email invoice lunas resmi ke pembeli.
     - Pesanan langsung berpindah ke tab "Perlu Dikirim" sehingga admin bisa memproses penjemputan ekspedisi Biteship / cetak label.
   - **Tombol Merah: "Tolak / Minta Upload Ulang"**
     - Membuka modal kecil untuk memasukkan alasan (misal: "Dana belum masuk ke mutasi rekening", "Foto bukti terpotong/buram").
     - Memperbarui `payment_status = 'unpaid'` dan mengisi `payment_rejection_note`.
     - Memberi kesempatan pembeli mengunggah bukti yang sah.

---

## 6. Penanganan Storage & Keamanan
- File gambar bukti diunggah menggunakan validasi ketat: `image`, `mimes:jpg,jpeg,png,webp`, `max:5120`.
- Disimpan di disk `public` pada folder `payment_proofs/`.
- Nama file di-hash dan diberi prefix nomor pesanan yang aman dari penimpaan atau traversal path.
- Endpoint upload diproteksi middleware `auth` dan memastikan pembeli hanya dapat mengunggah bukti untuk pesanannya sendiri.
- Endpoint verifikasi admin diproteksi middleware admin (`isAdmin`).

---

## 7. Verifikasi & Pengujian
1. **Uji Checkout Manual BCA:**
   - Buat pesanan dengan memilih opsi Transfer Manual BCA.
   - Verifikasi pesanan terbuat tanpa error Midtrans dan tanpa Snap Token.
2. **Uji Upload Bukti Transfer:**
   - Unggah gambar bukti pembayaran dari sisi pembeli.
   - Verifikasi status berubah menjadi `pending_verification` dan gambar tersimpan di storage.
3. **Uji Verifikasi Admin:**
   - Buka halaman pesanan di admin, lihat foto bukti transfer.
   - Klik "Konfirmasi Dana Masuk", pastikan status menjadi `paid` dan `processing`.
   - Pastikan pesanan dapat diproses kirim ke Biteship seperti biasa.
4. **Uji Regresi Midtrans:**
   - Lakukan simulasi / verifikasi alur Midtrans (QRIS, VA) tetap normal dan tidak terpengaruh.
