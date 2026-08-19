<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ulasan produk dari pembeli.
 *
 * Ulasan diikat ke BARIS PESANAN (order_item_id), bukan sekadar ke produk.
 * Itu yang membuat ulasan di sini tidak bisa dikarang: setiap ulasan selalu
 * bisa ditelusuri ke satu pembelian yang benar-benar terjadi, lengkap dengan
 * ukuran atau varian yang dibeli. Karena satu baris pesanan hanya boleh
 * menghasilkan satu ulasan, kuncinya dibuat unik — bukan sekadar dijaga di
 * kode aplikasi, sebab penjagaan di kode bisa dilewati oleh dua permintaan
 * yang tiba bersamaan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('rating');          // 1 sampai 5
            $table->text('comment')->nullable();            // boleh dikosongkan
            $table->json('photos')->nullable();             // jalur berkas, maksimal 3

            /*
             * Ulasan tidak pernah dihapus diam-diam oleh admin, melainkan
             * disembunyikan — supaya keputusan itu punya jejak dan bisa
             * ditarik kembali kalau ternyata keliru.
             */
            $table->boolean('is_hidden')->default(false);
            $table->string('hidden_reason')->nullable();
            $table->foreignId('hidden_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hidden_at')->nullable();

            $table->timestamps();

            // Halaman produk selalu meminta ulasan yang tampil untuk satu
            // produk, diurutkan dari yang terbaru.
            $table->index(['product_id', 'is_hidden', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
