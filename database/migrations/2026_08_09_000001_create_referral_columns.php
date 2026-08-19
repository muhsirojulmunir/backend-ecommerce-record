<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kode referal.
 *
 * Setiap pembeli yang pernah menuntaskan pembayaran mendapat satu kode
 * berbentuk RECORD-NAMAPENGGUNA. Kode itu tetap seumur akun — berapa kali pun
 * ia berbelanja, kodenya tidak berganti.
 *
 * Nilai diskon dan komisi disimpan sebagai angka pada pesanan, bukan dihitung
 * ulang saat ditampilkan. Persentasenya bisa berubah kelak, dan pesanan lama
 * harus tetap memperlihatkan potongan yang benar-benar diterima pembeli saat
 * itu — bukan hasil hitungan dengan aturan baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 60)->nullable()->unique()->after('rpay_balance');
            $table->timestamp('referral_issued_at')->nullable()->after('referral_code');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Kode yang dipakai pembeli pada pesanan ini.
            $table->string('referral_code_used', 60)->nullable()->after('completed_at');

            // Pemilik kode. Sengaja nullOnDelete: riwayat potongan pada pesanan
            // tetap benar meski akun pemilik kodenya kelak dihapus.
            $table->foreignId('referrer_id')->nullable()->after('referral_code_used')
                ->constrained('users')->nullOnDelete();

            $table->decimal('referral_discount', 14, 2)->default(0)->after('referrer_id');
            $table->decimal('referral_commission', 14, 2)->default(0)->after('referral_discount');

            $table->index('referral_code_used');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['referral_code_used']);
            $table->dropConstrainedForeignId('referrer_id');
            $table->dropColumn(['referral_code_used', 'referral_discount', 'referral_commission']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['referral_code']);
            $table->dropColumn(['referral_code', 'referral_issued_at']);
        });
    }
};
