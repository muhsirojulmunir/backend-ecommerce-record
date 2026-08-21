<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan saldo Biteship.
 *
 * Biteship TIDAK menyediakan endpoint untuk membaca saldo — sudah diperiksa
 * langsung ke API-nya, dan semua kandidat alamat menjawab "Route not found".
 * Karena itu angkanya tidak bisa diambil otomatis.
 *
 * Gantinya: admin mencatat saldo yang tertera di dasbor Biteship setiap kali
 * mengisi ulang, lalu sistem mengurangi ongkir tiap pengiriman yang terbit
 * sejak saat itu. Hasilnya PERKIRAAN, bukan angka pasti — biaya kecil seperti
 * Tracking Rp 10 dan Rates Rp 5 tidak ikut terhitung, sehingga saldo
 * sebenarnya selalu sedikit lebih rendah daripada perkiraan ini.
 *
 * Perkiraan itu dipakai untuk memperingatkan LEBIH AWAL. Kepastiannya tetap
 * datang dari galat Biteship yang sesungguhnya saat saldo benar-benar kurang.
 *
 * Riwayatnya disimpan, tidak ditimpa, supaya pola pemakaian saldo bisa dilihat
 * dan salah ketik bisa ditelusuri.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biteship_saldo', function (Blueprint $table) {
            $table->id();

            // Saldo yang tertera di dasbor Biteship saat dicatat.
            $table->unsignedBigInteger('saldo_tercatat');

            $table->timestamp('dicatat_pada');
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('catatan')->nullable();

            $table->timestamps();

            // Yang dipakai selalu catatan terbaru.
            $table->index('dicatat_pada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biteship_saldo');
    }
};
