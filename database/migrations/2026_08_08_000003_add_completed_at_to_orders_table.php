<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Batas waktu pengajuan pengembalian dihitung sejak pesanan dinyatakan
 * selesai. Tanpa kolom ini, patokannya terpaksa memakai updated_at — yang
 * ikut berubah setiap kali baris pesanan disentuh, sehingga batas waktunya
 * bisa mundur sendiri tanpa disadari.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('cancelled_at');
        });

        // Pesanan lama yang sudah selesai diberi patokan dari updated_at,
        // supaya batas waktunya tetap masuk akal.
        Schema::getConnection()
            ->table('orders')
            ->where('status', 'completed')
            ->whereNull('completed_at')
            ->update(['completed_at' => Schema::getConnection()->raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
