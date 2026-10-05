<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('voucher_id')->nullable()->after('referral_commission')->constrained('vouchers')->nullOnDelete();
            $table->unsignedBigInteger('voucher_discount')->default(0)->after('voucher_id')->comment('Nominal potongan voucher dalam Rupiah');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voucher_id');
            $table->dropColumn('voucher_discount');
        });
    }
};
