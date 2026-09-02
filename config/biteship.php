<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kunci API & URL Biteship
    |--------------------------------------------------------------------------
    |
    | Kunci API didapatkan dari dasbor Biteship (https://dashboard.biteship.com).
    | Digunakan untuk request pickup kurir, cek ongkir, dan pelacakan AWB.
    |
    */
    'api_key' => env('BITESHIP_API_KEY'),
    'api_url' => env('BITESHIP_API_URL', 'https://api.biteship.com/v1'),

    /*
    |--------------------------------------------------------------------------
    | Ambang peringatan saldo
    |--------------------------------------------------------------------------
    |
    | Saldo Biteship dipakai untuk membayar ongkir tiap pengiriman, ditambah
    | biaya kecil per panggilan API (Tracking Rp 10, Rates Rp 5, Maps Rp 2).
    | Kalau habis, resi gagal terbit dan pesanan tidak bisa diproses sama sekali.
    |
    | Peringatan muncul di halaman admin begitu perkiraan saldo turun di bawah
    | angka ini.
    |
    */
    'ambang_peringatan' => (int) env('BITESHIP_AMBANG_SALDO', 10_000),

    /*
    |--------------------------------------------------------------------------
    | Lama peringatan "saldo habis" bertahan
    |--------------------------------------------------------------------------
    |
    | Ketika Biteship benar-benar menolak karena saldo kurang, penandanya
    | disimpan selama ini (dalam menit) supaya peringatannya tetap terlihat
    | walau admin berpindah halaman. Penandanya dihapus sendiri begitu ada
    | pengiriman yang kembali berhasil.
    |
    */
    'menit_tanda_habis' => (int) env('BITESHIP_MENIT_TANDA_HABIS', 180),

];
