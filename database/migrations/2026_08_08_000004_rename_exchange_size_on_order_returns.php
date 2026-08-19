<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penukaran tidak lagi terbatas pada ukuran.
 *
 * Pembeli kini boleh meminta barang pengganti yang berbeda — misalnya warna
 * lain atau model lain — sehingga isian ini bisa berbunyi "ukuran 43 warna
 * hitam", bukan sekadar "43". Nama kolom "exchange_size" jadi menyesatkan
 * bagi siapa pun yang membaca skema ini nanti, dan panjang 255 karakter
 * diperlukan untuk menampung kalimat pendek.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->renameColumn('exchange_size', 'exchange_request');
        });

        Schema::table('order_returns', function (Blueprint $table) {
            $table->string('exchange_request', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->renameColumn('exchange_request', 'exchange_size');
        });
    }
};
