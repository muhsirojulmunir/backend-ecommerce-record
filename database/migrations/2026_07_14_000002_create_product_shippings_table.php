<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_shippings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('weight_gram')->default(0);           // Berat dalam gram
            $table->decimal('package_length', 8, 2)->default(0); // cm
            $table->decimal('package_width', 8, 2)->default(0);  // cm
            $table->decimal('package_height', 8, 2)->default(0); // cm
            $table->json('courier_providers')->nullable();        // ["jne","jnt","sicepat"]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_shippings');
    }
};
