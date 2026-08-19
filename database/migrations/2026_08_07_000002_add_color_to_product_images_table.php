<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            // Nama warna varian yang diwakili foto ini.
            // NULL berarti foto umum yang tampil untuk semua warna.
            $table->string('color')->nullable()->after('image_path');
            $table->index(['product_id', 'color']);
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'color']);
            $table->dropColumn('color');
        });
    }
};
