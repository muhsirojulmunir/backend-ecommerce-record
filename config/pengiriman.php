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
        'lintang' => (float) env('STORE_LATITUDE', -7.2575),
        'bujur'   => (float) env('STORE_LONGITUDE', 112.7521),
        'kota'    => env('STORE_CITY', 'Surabaya'),
    ],

];
