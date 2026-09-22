<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Referensi unik dari Duitku untuk cek status pembayaran
            $table->string('duitku_reference')->nullable()->after('payment_method');
            // URL pembayaran yang diterbitkan Duitku (redirect VA/QRIS)
            $table->string('duitku_payment_url', 1000)->nullable()->after('duitku_reference');
            // Nomor VA atau kode bayar yang ditampilkan ke customer
            $table->string('duitku_va_number')->nullable()->after('duitku_payment_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['duitku_reference', 'duitku_payment_url', 'duitku_va_number']);
        });
    }
};
