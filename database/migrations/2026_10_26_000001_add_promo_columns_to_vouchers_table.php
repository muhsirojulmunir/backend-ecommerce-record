<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'type')) {
                $table->string('type', 20)->default('single')->after('code')->comment('single: kupon sekali pakai, promo: kode promo massal');
            }
            if (!Schema::hasColumn('vouchers', 'total_quota')) {
                $table->unsignedInteger('total_quota')->nullable()->after('amount')->comment('Batas kuota pemakaian promo (null = unlimited)');
            }
            if (!Schema::hasColumn('vouchers', 'used_count')) {
                $table->unsignedInteger('used_count')->default(0)->after('total_quota')->comment('Total kali voucher telah digunakan');
            }
            if (!Schema::hasColumn('vouchers', 'limit_per_user')) {
                $table->unsignedSmallInteger('limit_per_user')->default(1)->after('used_count')->comment('Batas pemakaian per akun user');
            }
            if (!Schema::hasColumn('vouchers', 'limit_per_device')) {
                $table->unsignedSmallInteger('limit_per_device')->default(1)->after('limit_per_user')->comment('Batas pemakaian per perangkat (device)');
            }

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'total_quota', 'used_count', 'limit_per_user', 'limit_per_device']);
        });
    }
};
