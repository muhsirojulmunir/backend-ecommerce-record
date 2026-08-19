<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bukti pengajuan pengembalian.
 *
 * Tiga berkas wajib: foto resi, foto paket, dan video unboxing. Ketiganya
 * disimpan sebagai jalur berkas, bukan ditanam ke basis data — video bisa
 * berukuran puluhan megabita dan tidak ada gunanya membebani setiap kueri
 * pesanan dengan muatan sebesar itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->string('receipt_photo')->nullable()->after('reason');
            $table->string('package_photo')->nullable()->after('receipt_photo');
            $table->string('unboxing_video')->nullable()->after('package_photo');
            $table->unsignedSmallInteger('video_duration')->nullable()->after('unboxing_video');
        });
    }

    public function down(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->dropColumn(['receipt_photo', 'package_photo', 'unboxing_video', 'video_duration']);
        });
    }
};
