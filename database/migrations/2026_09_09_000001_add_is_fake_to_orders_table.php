<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Kolom penanda apakah pesanan ini dibuat oleh seeder fiktif
            // (untuk ulasan dummy) atau benar-benar dari pembeli nyata.
            // Default false = semua pesanan lama dianggap real.
            $table->boolean('is_fake')->default(false)->after('grand_total');
        });

        // Tandai pesanan lama yang user-nya adalah akun fiktif dari seeder.
        // Akun fiktif seeder selalu punya pola '.fake\d+@mail.test' di emailnya.
        DB::statement("
            UPDATE orders o
            INNER JOIN users u ON u.id = o.user_id
            SET o.is_fake = 1
            WHERE u.email LIKE '%fake%@mail.test'
        ");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('is_fake');
        });
    }
};
