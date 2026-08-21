<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kapan pengiriman diasuransikan
    |--------------------------------------------------------------------------
    |
    | 'otomatis' — diasuransikan HANYA bila ganti rugi tanpa asuransi tidak
    |              cukup menutup nilai barang. Ini bawaannya, dan biasanya yang
    |              paling masuk akal secara uang.
    | 'selalu'   — semua pengiriman diasuransikan, berapa pun nilainya.
    | 'mati'     — tidak ada yang diasuransikan.
    |
    */
    'mode' => env('ASURANSI_MODE', 'otomatis'),

    /*
    |--------------------------------------------------------------------------
    | Aturan ganti rugi TANPA asuransi
    |--------------------------------------------------------------------------
    |
    | Tanpa asuransi, penggantian dibatasi pada yang TERKECIL antara sekian kali
    | ongkos kirim atau nilai barangnya, dengan plafon tertentu.
    |
    | Angka ini dipakai untuk memutuskan apakah sebuah pengiriman perlu
    | diasuransikan. Cocokkan dengan ketentuan Biteship yang berlaku — sebaiknya
    | dikonfirmasi sekali ke support mereka, karena tiap kurir bisa berbeda.
    |
    */
    'kelipatan_ongkir' => (int) env('ASURANSI_KELIPATAN_ONGKIR', 10),
    'plafon_tanpa_asuransi' => (int) env('ASURANSI_PLAFON', 1_000_000),

    /*
    |--------------------------------------------------------------------------
    | Nilai barang terkecil yang dipertimbangkan
    |--------------------------------------------------------------------------
    |
    | Barang di bawah nilai ini tidak diasuransikan meski perhitungan di atas
    | menyarankannya. Premi untuk barang murah sering kalah oleh premi minimum
    | kurir, sehingga biayanya tidak sebanding dengan risikonya.
    |
    */
    'nilai_minimum' => (int) env('ASURANSI_NILAI_MINIMUM', 50_000),

    /*
    |--------------------------------------------------------------------------
    | Batas atas premi yang masih diterima
    |--------------------------------------------------------------------------
    |
    | Pengaman terhadap tarif tak terduga: bila premi melebihi persentase ini
    | dari nilai barang, pengiriman tetap dibuat TANPA asuransi dan dicatat di
    | berkas log untuk diperiksa. Tanpa pengaman ini, perubahan tarif di sisi
    | Biteship bisa diam-diam menggerus keuntungan tiap pesanan.
    |
    */
    'batas_premi_persen' => (float) env('ASURANSI_BATAS_PERSEN', 2.0),

];
