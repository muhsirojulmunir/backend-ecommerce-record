<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pengembalian barang berjalan bertahap, bukan sekali putus.
 *
 * Sebelumnya pengajuan hanya punya tiga keadaan: menunggu, disetujui,
 * ditolak — dan dana langsung cair begitu disetujui. Itu keliru: barangnya
 * belum tentu sampai kembali, dan belum diperiksa. Toko bisa kehilangan uang
 * sekaligus barangnya.
 *
 * Tahapannya sekarang:
 *
 *   pending      → menunggu admin meninjau pengajuan
 *   approved     → disetujui, pembeli diminta mengirim barangnya kembali
 *   shipped_back → pembeli sudah mengirim, nomor resinya tercatat
 *   received     → barang sampai di toko dan sedang diperiksa
 *   completed    → lolos periksa; dana dikembalikan / barang tukar dikirim
 *   rejected     → ditolak, baik saat peninjauan awal maupun setelah periksa
 */
return new class extends Migration
{
    public function up(): void
    {
        // MySQL memperlakukan enum sebagai daftar tetap, jadi kolomnya
        // ditulis ulang. VARCHAR dipilih agar penambahan tahap berikutnya
        // tidak perlu mengubah skema lagi.
        DB::statement("ALTER TABLE `order_returns` MODIFY `status` VARCHAR(20) NOT NULL DEFAULT 'pending'");

        Schema::table('order_returns', function (Blueprint $table) {
            // Kapan admin menyetujui — patokan tenggat pembeli mengirim barang.
            $table->timestamp('approved_at')->nullable()->after('status');

            // Pengiriman balik oleh pembeli. Ongkosnya ditanggung pembeli,
            // jadi kurirnya bebas dan resinya diisi sendiri.
            $table->string('return_courier')->nullable()->after('approved_at');
            $table->string('return_tracking_number')->nullable()->after('return_courier');
            $table->timestamp('shipped_back_at')->nullable()->after('return_tracking_number');

            // Barang sampai kembali di toko.
            $table->timestamp('received_at')->nullable()->after('shipped_back_at');

            // Hasil pemeriksaan barang setelah sampai — inilah yang menentukan
            // pengembalian dana jadi cair atau ditolak.
            $table->text('inspection_notes')->nullable()->after('received_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->dropColumn([
                'approved_at', 'return_courier', 'return_tracking_number',
                'shipped_back_at', 'received_at', 'inspection_notes',
            ]);
        });

        DB::statement("ALTER TABLE `order_returns` MODIFY `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
    }
};
