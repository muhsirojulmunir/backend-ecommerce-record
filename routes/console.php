<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Batalkan pesanan pending yang belum dibayar melebihi batas waktu 24 jam setiap jam
Schedule::command('pesanan:batalkan-kedaluwarsa')->hourly();
