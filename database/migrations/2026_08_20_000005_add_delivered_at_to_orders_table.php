<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Waktu paket dinyatakan sampai oleh kurir.
 *
 * Berbeda dari `completed_at`, yang menandai pembeli MENGONFIRMASI pesanannya
 * selesai. Keduanya perlu dibedakan: kurir bisa menyatakan paket sampai hari
 * ini sementara pembeli baru menekan tombol konfirmasi tiga hari kemudian —
 * dan tenggat pengajuan pengembalian dihitung dari konfirmasi itu.
 *
 * Diisi oleh webhook Biteship. Status pesanannya sengaja TIDAK ikut diubah:
 * daftar status yang sah di sistem ini hanya pending, processing, shipped,
 * completed, dan cancelled — menambahkan "delivered" akan merusak penyaring
 * dan label status di seluruh halaman. Menandai selesai secara otomatis juga
 * akan melewati konfirmasi pembeli dan memulai tenggat pengembalian lebih awal
 * daripada seharusnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivered_at');
        });
    }
};
