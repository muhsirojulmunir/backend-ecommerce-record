<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lokasi Gudang / Toko
    |--------------------------------------------------------------------------
    |
    | Titik asal pengiriman, sekaligus TUJUAN bagi paket pengembalian yang
    | dikirim balik pembeli. Dipakai panel admin untuk menggambar peta tujuan
    | paket kembali.
    |
    | Nilainya sengaja dibaca dari peubah lingkungan yang sama persis dengan
    | yang dipakai aplikasi toko (STORE_LATITUDE, STORE_LONGITUDE, STORE_CITY),
    | supaya kedua aplikasi selalu menunjuk gudang yang sama. Kalau angkanya
    | disalin terpisah, cepat atau lambat salah satunya akan tertinggal saat
    | gudangnya pindah.
    |
    */

    'toko' => [
        'lintang' => (float) env('STORE_LATITUDE', -7.2275),
        'bujur'   => (float) env('STORE_LONGITUDE', 112.7865),
        'kota'    => env('STORE_CITY', 'Surabaya'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Berat satu barang saat dikirim (gram)
    |--------------------------------------------------------------------------
    |
    | Satu-satunya sumber kebenaran untuk berat kiriman. Angka INI yang
    | dilaporkan ke Biteship saat pesanan dibuat, dan angka ini pula yang
    | tercetak di label — supaya keduanya tidak pernah berbeda.
    |
    | Sebelumnya ada tiga angka berbeda untuk barang yang sama: 500 gram di
    | muatan ke Biteship, 400 gram di label cetakan, dan 800 gram di
    | config/pengiriman.php milik toko. Berat yang dilaporkan lebih ringan
    | dari kenyataan bisa memicu tagihan susulan dari kurir; yang lebih berat
    | membuat ongkir kemahalan.
    |
    | PERIKSA ANGKA INI. Timbang satu sepatu beserta kardus dan pembungkusnya,
    | lalu sesuaikan. Nilai bawaan di sini hanya meneruskan angka yang selama
    | ini dipakai ke Biteship, bukan hasil pengukuran.
    |
    */
    'berat_kirim_gram' => (int) env('BERAT_KIRIM_GRAM', 500),

];
