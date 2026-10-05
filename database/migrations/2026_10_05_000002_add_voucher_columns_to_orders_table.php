<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'voucher_id')) {
                $table->unsignedBigInteger('voucher_id')->nullable()->after('referral_commission')->index();
            }
            if (!Schema::hasColumn('orders', 'voucher_discount')) {
                $table->unsignedBigInteger('voucher_discount')->default(0)->after('voucher_id')->comment('Nominal potongan voucher dalam Rupiah');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'voucher_id')) {
                $table->dropIndex(['voucher_id']);
                $table->dropColumn('voucher_id');
            }
            if (Schema::hasColumn('orders', 'voucher_discount')) {
                $table->dropColumn('voucher_discount');
            }
        });
    }
};
