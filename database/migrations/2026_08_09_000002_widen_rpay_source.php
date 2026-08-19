<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sumber transaksi R_Pay dilonggarkan dari enum menjadi teks.
 *
 * Komisi referal menambah dua jenis sumber baru, dan setiap penambahan
 * berikutnya akan memaksa perubahan skema lagi kalau kolomnya tetap enum.
 * Daftar sumber yang berlaku tetap dijaga di sisi kode.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `rpay_transactions` MODIFY `source` VARCHAR(30) NOT NULL");
    }

    public function down(): void
    {
        DB::table('rpay_transactions')
            ->whereIn('source', ['referral', 'referral_reversal'])
            ->update(['source' => 'adjustment']);

        DB::statement(
            "ALTER TABLE `rpay_transactions` MODIFY `source` "
            . "ENUM('refund','checkout','withdrawal','reversal','adjustment') NOT NULL"
        );
    }
};
