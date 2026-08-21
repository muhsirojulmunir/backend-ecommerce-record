<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Biaya asuransi pengiriman yang dibayarkan ke Biteship.
 *
 * Preminya baru diketahui saat resi terbit, bukan saat pembeli membayar, jadi
 * kolomnya terisi belakangan. Nilainya diambil apa adanya dari jawaban Biteship
 * (`courier.insurance.fee`) — bukan dihitung sendiri dari persentase, karena
 * tiap kurir punya tarif dan premi minimumnya masing-masing.
 *
 * Disimpan supaya penghasilan bersih per pesanan tetap jujur: premi itu uang
 * toko yang keluar, sama seperti ongkir dan biaya Midtrans.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('shipping_insurance_fee', 12, 2)
                ->default(0)
                ->after('shipping_markup_profit');

            // Nilai barang yang diasuransikan. Nol berarti pengiriman ini
            // memang sengaja tidak diasuransikan.
            $table->decimal('shipping_insurance_value', 12, 2)
                ->default(0)
                ->after('shipping_insurance_fee');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_insurance_fee', 'shipping_insurance_value']);
        });
    }
};
