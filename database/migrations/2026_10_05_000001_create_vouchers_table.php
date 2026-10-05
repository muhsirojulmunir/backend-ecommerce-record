<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('Kode unik voucher (4 karakter)');
            $table->unsignedBigInteger('amount')->comment('Nominal potongan dalam Rupiah');
            $table->boolean('is_used')->default(false);
            $table->foreignId('used_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('used_at')->nullable();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('expires_at')->nullable()->comment('Tanggal kadaluarsa (opsional)');
            $table->string('batch_label', 100)->nullable()->comment('Label batch untuk pengelompokan');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_used', 'expires_at']);
            $table->index('batch_label');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
