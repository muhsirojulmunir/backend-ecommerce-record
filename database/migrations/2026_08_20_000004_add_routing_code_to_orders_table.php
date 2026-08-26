<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kode sortir dan id pelacakan dari Biteship.
 *
 * `routing_code` adalah kode yang dipakai gudang kurir untuk mengarahkan paket
 * ke kota tujuan. Inilah yang membedakan label resmi dari label karangan:
 * tanpa kode itu, paket bisa salah sortir walau nomor resinya benar.
 *
 * Keduanya dikembalikan Biteship saat pesanan pengiriman dibuat, tetapi selama
 * ini dibuang begitu saja — yang disimpan hanya nomor resinya. Sekarang ikut
 * disimpan supaya label yang dicetak Seller Center memuat informasi yang sama
 * dengan label resmi Biteship.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_routing_code', 60)
                ->nullable()
                ->after('tracking_number');

            // Id internal Biteship untuk pesanan pengiriman ini. Dipakai untuk
            // membuka halaman pesanannya langsung di dasbor Biteship.
            $table->string('biteship_order_id', 60)
                ->nullable()
                ->after('shipping_routing_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_routing_code', 'biteship_order_id']);
        });
    }
};
