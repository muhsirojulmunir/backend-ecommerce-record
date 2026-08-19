<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Titik fokus gambar dalam persen, dipakai sebagai object-position
            // supaya admin bisa menggeser bagian gambar mana yang tampil.
            $table->unsignedTinyInteger('image_position_x')->default(50)->after('image');
            $table->unsignedTinyInteger('image_position_y')->default(50)->after('image_position_x');
            // Perbesaran gambar, 1.00 = pas bingkai
            $table->decimal('image_zoom', 4, 2)->default(1.00)->after('image_position_y');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['image_position_x', 'image_position_y', 'image_zoom']);
        });
    }
};
