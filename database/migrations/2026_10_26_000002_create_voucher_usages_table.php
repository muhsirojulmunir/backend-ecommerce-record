<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('voucher_usages')) {
            Schema::create('voucher_usages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->string('device_id', 64)->comment('Hash fingerprint perangkat client');
                $table->string('device_label', 120)->nullable()->comment('Label terdeteksi, cth: Windows 11 · Chrome');
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->unsignedBigInteger('discount_amount')->default(0);
                $table->timestamps();

                $table->index(['voucher_id', 'device_id']);
                $table->index(['voucher_id', 'user_id']);
                $table->index('order_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_usages');
    }
};
