<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Duitku Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi Duitku Payment Gateway.
    | Isi DUITKU_MERCHANT_CODE dan DUITKU_API_KEY di file .env.
    |
    | Dokumentasi: https://docs.duitku.com/api/id/
    |
    */

    'merchant_code' => env('DUITKU_MERCHANT_CODE', ''),
    'api_key'       => env('DUITKU_API_KEY', ''),

    /*
    | Sandbox mode: true = gunakan environment testing Duitku
    | Set DUITKU_SANDBOX=false untuk production
    */
    'sandbox' => env('DUITKU_SANDBOX', true),

    /*
    | Waktu kedaluarsa transaksi dalam menit (default 24 jam)
    */
    'expiry_minutes' => env('DUITKU_EXPIRY_MINUTES', 1440),

    /*
    |--------------------------------------------------------------------------
    | Status Maintenance Metode Pembayaran
    |--------------------------------------------------------------------------
    |
    | Metode pembayaran yang sedang dalam maintenance (tidak tersedia).
    | Isi dengan kode metode Duitku (BC, M2, VA, dst) atau nama metode kustom.
    | Pembayaran 'MANUAL_BCA' dan 'COD' dikecualikan dan selalu aktif.
    |
    */
    'maintenance_methods' => [
        'BC', 'M2', 'VA', 'I1', 'B1', 'BT', 'A1', 'AG', 'NC', 'SA', 'QR', 'FT', 'IR',
    ],

    /*
    | Metode yang aktif dan dapat digunakan oleh pelanggan.
    | Ubah nilai ini saat gateway sudah siap produksi.
    */
    'active_methods' => ['MANUAL_BCA', 'COD'],
];
