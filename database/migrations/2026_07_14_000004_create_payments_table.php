<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method');                          // transfer, cod, gateway
            $table->string('payment_gateway')->nullable();             // midtrans, xendit, null
            $table->string('transaction_id')->nullable();              // ID dari payment gateway
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_url')->nullable();                 // URL redirect ke gateway
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();                      // Payload dari gateway
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
