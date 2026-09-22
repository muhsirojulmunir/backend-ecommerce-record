# Spesifikasi Desain: Fitur Perbandingan Atensi Seksi Website Hari ke Hari (Day-over-Day Dwell Time Comparison)

**Tanggal:** 2026-09-22  
**Status:** Disetujui (Menunggu Review Akhir Spesifikasi)  
**Terkait:** Log Aktivitas Admin (`AdminWebActivityLogController.php`, `activity-logs.blade.php`)

---

## 1. Latar Belakang & Tujuan
Pada halaman Log Aktivitas Admin Seller Center, sudah terdapat fitur pemantauan *Evaluasi Atensi Seksi Website* (*Dwell Time*) yang merekam seksi mana saja yang paling lama diperhatikan oleh pengunjung toko (misal: Banner Promo, Koleksi Terlaris, Ulasan, Footer, dsb.).

Saat ini, data tersebut disajikan dalam bentuk akumulasi total durasi satu periode saja. Admin memerlukan fitur **perbandingan dari hari ke hari (*Day-over-Day / DoD Comparison*)** agar dapat:
1. Mengetahui tren atensi setiap seksi per harinya secara detail.
2. Membandingkan performa hari ini terhadap hari sebelumnya (apakah minat pengunjung terhadap seksi tertentu naik atau turun).
3. Menganalisis efektivitas perubahan konten website dari waktu ke waktu (misal setelah mengganti banner atau mengubah tata letak produk rekomendasi).

---

## 2. Logika Pengambilan & Agregasi Data (*Backend Logic*)

### A. Penentuan Rentang Tanggal Komparasi
Sistem mendukung fleksibilitas filter yang sudah ada di kartu atensi seksi:
1. **Kasus 1 Tanggal Spesifik (misal filter: `2026-09-20`):**
   * Otomatis menyandingkan hari yang dipilih ($T$) dengan 1 hari sebelumnya ($T-1$, yaitu `2026-09-19`) sebagai titik acuan komparasi (*baseline*).
   * Kolom tabel yang dihasilkan: `[19 Sep (H-1)]` dan `[20 Sep (Hari Dipilih)]`.
2. **Kasus Rentang Tanggal (misal filter: `2026-09-20` s/d `2026-09-25`):**
   * Mengambil data untuk seluruh tanggal dalam rentang tersebut berurutan: `[20 Sep, 21 Sep, 22 Sep, 23 Sep, 24 Sep, 25 Sep]`.
   * Di latar belakang, sistem juga mengambil tanggal $T_{\text{start}}-1$ (19 Sep) sebagai acuan pembanding hari pertama agar tanggal 20 Sep tetap memiliki persentase tren $(\Delta\%)$.
3. **Kasus Default / Cepat (`7d`, `today`, `30d`):**
   * Default mode komparasi menampilkan 7 hari terakhir: `[H-6, H-5, H-4, H-3, H-2, Kemarin, Hari Ini]`.
   * Jika filter `today`: Menampilkan Kemarin (H-1) vs Hari Ini.

### B. Kueri & Agregasi Database
* **Sumber Data:** Tabel `activity_log` dengan kondisi:
  * `log_name = 'evaluasi_web'`
  * `event = 'dwell'`
  * `created_at` di antara tanggal awal pembanding s/d tanggal akhir filter.
* **Pengelompokan:**
  * Kelompokkan per `tanggal` (`Y-m-d`) dan per `section` (`properties->section`).
  * Nilai metrik per sel `[seksi][tanggal]`:
    * `total_seconds`: jumlah detik durasi atensi.
    * `views`: total frekuensi seksi tersebut dilihat $\ge 3$ detik.
    * `unique_viewers_count`: jumlah pengunjung unik (kombinasi user ID atau IP tamu).

### C. Rumus Persentase Pertumbuhan Hari ke Hari (*Day-over-Day Delta %*)
Untuk seksi $S$ pada tanggal $T$ dibandingkan dengan tanggal $T-1$:
$$\Delta\% = \begin{cases} 
\frac{\text{detik}_T - \text{detik}_{T-1}}{\text{detik}_{T-1}} \times 100\% & \text{jika detik}_{T-1} > 0 \\
+100\% \text{ (Baru)} & \text{jika detik}_{T-1} = 0 \text{ dan detik}_T > 0 \\
-100\% & \text{jika detik}_{T-1} > 0 \text{ dan detik}_T = 0 \\
0\% & \text{jika detik}_{T-1} = 0 \text{ dan detik}_T = 0
\end{cases}$$

### D. Indikator & Metrik Tambahan
* **Total Web per Hari:** Akumulasi detik seluruh seksi di tanggal $T$.
* **Seksi Paling Melonjak (*Top Gainer*):** Seksi dengan peningkatan durasi/persentase tertinggi pada hari terbaru dibanding hari sebelumnya.
* **Seksi Terpopuler (*Top Performer*):** Seksi dengan durasi tertinggi pada hari terbaru.

---

## 3. Desain Antarmuka Pengguna (*UI/UX Layout*)

File Target: `backend-ecommerce-record/resources/views/admin/activity-logs.blade.php`

### A. Tab Switcher Modern di Header Kartu Atensi
Di dalam header kartu *Evaluasi Atensi Seksi Website*, sediakan sub-tab menggunakan Alpine.js (`dwellViewMode = 'summary' | 'comparison'`):
* 🔘 **Ringkasan Seksi:** Menampilkan tampilan kartu baris & progress bar agregat yang sudah ada.
* 🔘 **Perbandingan Harian (DoD):** Menampilkan tabel matriks komparasi hari ke hari yang baru.

### B. 3 Kartu Ringkasan Cepat (*Executive Highlights*)
Tepat di atas tabel perbandingan harian:
1. **Atensi Teratas Hari Terakhir:** Nama seksi + durasi total + % kontribusi.
2. **Lonjakan Tertinggi (*Top Gainer*):** Nama seksi dengan kenaikan $\%$ tertinggi dibanding hari sebelumnya.
3. **Komparasi Total Toko:** Total menit toko pada hari terakhir dibanding hari sebelumnya (contoh: `2j 15m (+24.5% vs H-1)`).

### C. Tabel Matriks Harian (*Day-by-Day Comparison Matrix*)
* **Kolom Kiri (Fixed / Sticky):**
  * Ikon seksi, nama label seksi website, dan penanda halaman (misal: `Home`, `Koleksi`).
* **Kolom-kolom Tanggal Berurutan:**
  * Header: Format singkatan hari & tanggal (misal: `Kam, 18 Sep` ... `Sen, 21 Sep` ... `Sel, 22 Sep`). Hari terakhir diberi penanda badge `Hari Ini` atau `Aktif`.
  * Sel Data:
    * Angka durasi tebal: misal `15m 30d`.
    * Lencana tren kecil di bawahnya:
      * Hijau muda: `+18% ↗️`
      * Merah muda: `-12% ↘️`
      * Abu-abu: `0%` atau `-` jika data stabil/belum ada perbandingan.
* **Kolom Rata-rata / Total Periode:**
  * Memberikan gambaran performa seksi tersebut sepanjang rentang yang dipilih.
* **Baris Footer Tabel (Total Seluruh Web):**
  * Menampilkan baris total akumulasi waktu pengunjung untuk tiap hari dan tren pertumbuhan harian toko secara menyeluruh.

---

## 4. Rencana Verifikasi & Pengujian
1. **Uji Kasus Filter 1 Tanggal:**
   * Filter tanggal tunggal (misal 20 Sep), verifikasi bahwa kolom menampilkan Tanggal 19 Sep dan 20 Sep beserta $\Delta\%$-nya.
2. **Uji Kasus Rentang Tanggal (Multi-Hari):**
   * Filter 7 hari (misal 16 Sep - 22 Sep), verifikasi bahwa seluruh kolom tanggal tampil berurutan dengan perhitungan selisih berantai.
3. **Uji Penanganan Angka Nol (Edge Cases):**
   * Seksi yang baru dikunjungi hari ini (kemarin 0 detik) menampilkan status `+100% / Baru` tanpa error pembagian dengan nol (*division by zero*).
4. **Uji Tampilan Responsif (Mobile & Desktop):**
   * Tabel matriks memiliki scroll horizontal halus pada layar kecil tanpa merusak layout dashboard lainnya.
