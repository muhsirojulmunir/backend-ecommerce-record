<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Batas Ukuran Berkas Banner
    |--------------------------------------------------------------------------
    |
    | Banner adalah wajah toko di halaman depan, jadi berkasnya sengaja TIDAK
    | dimampatkan sama sekali — apa yang diunggah admin itulah yang tayang.
    | Itu sebabnya batasnya jauh lebih longgar daripada unggahan pembeli:
    | video promosi perlu tampil jernih, dan menurunkan mutunya demi menghemat
    | beberapa megabita justru merugikan yang dipromosikan.
    |
    | Bandingkan dengan bukti pengembalian (config/alasan-retur.php) yang
    | dibatasi 10 MB dan dimampatkan otomatis di peramban. Di sana yang
    | penting kejadiannya terekam, bukan ketajamannya; di sini sebaliknya.
    |
    | PENTING: batas ini tidak akan berlaku bila php.ini masih membatasi lebih
    | rendah. upload_max_filesize dan post_max_size harus lebih besar daripada
    | angka di bawah, kalau tidak PHP menolak berkasnya sebelum Laravel sempat
    | memeriksanya.
    |
    */

    'maks_video_mb' => 100,
    'maks_gambar_mb' => 10,

];
