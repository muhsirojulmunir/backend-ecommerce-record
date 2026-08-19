<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat unduhan ekspor pesanan.
 *
 * Berkasnya disimpan di cakram, dan baris di sini adalah catatannya: siapa
 * yang membuat, rentang tanggal mana, berapa baris, dan berapa besar. Dengan
 * begitu admin bisa mengunduh ulang berkas yang sama tanpa perlu menyusunnya
 * lagi dari awal — penyusunan ekspor besar memakan waktu.
 *
 * Riwayatnya dibatasi 10 berkas terbaru; yang lebih lama dibuang berikut
 * berkasnya. Tanpa batas itu, folder ekspor akan tumbuh selamanya oleh berkas
 * yang tidak pernah dibuka lagi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_exports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('file_name');
            $table->string('path');

            $table->date('dari')->nullable();
            $table->date('sampai')->nullable();

            $table->unsignedInteger('jumlah_pesanan')->default(0);
            $table->unsignedInteger('jumlah_baris')->default(0);
            $table->unsignedBigInteger('ukuran')->default(0);

            $table->timestamps();

            // Riwayat selalu dibaca dari yang terbaru.
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_exports');
    }
};
