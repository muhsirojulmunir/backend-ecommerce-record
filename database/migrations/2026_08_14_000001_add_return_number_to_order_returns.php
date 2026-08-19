<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nomor pengembalian.
 *
 * Sebelumnya pengajuan hanya dikenali lewat id basis data dan nomor pesanannya.
 * Itu cukup untuk mesin, tetapi tidak untuk manusia: admin dan pembeli perlu
 * satu nomor yang bisa disebut lewat pesan singkat tanpa ambigu — apalagi satu
 * pesanan bisa punya pengajuan pembatalan DAN pengembalian sekaligus.
 *
 * Formatnya mengikuti nomor pesanan yang sudah dipakai (ORD-YYYYMMDD-0001)
 * supaya polanya seragam dan mudah dikenali sekilas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->string('return_number', 32)->nullable()->unique()->after('id');
        });

        /*
         * Pengajuan yang sudah ada diberi nomor mundur, diurutkan menurut
         * waktu pembuatannya. Tanpa ini, pengajuan lama akan selamanya tanpa
         * nomor dan tampil kosong di panel admin.
         */
        $urutanPerTanggal = [];

        DB::table('order_returns')->orderBy('created_at')->orderBy('id')
            ->get(['id', 'created_at'])
            ->each(function ($baris) use (&$urutanPerTanggal) {
                $tanggal = $baris->created_at
                    ? date('Ymd', strtotime($baris->created_at))
                    : date('Ymd');

                $urutanPerTanggal[$tanggal] = ($urutanPerTanggal[$tanggal] ?? 0) + 1;

                DB::table('order_returns')->where('id', $baris->id)->update([
                    'return_number' => 'RET-' . $tanggal . '-'
                        . str_pad((string) $urutanPerTanggal[$tanggal], 4, '0', STR_PAD_LEFT),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->dropUnique(['return_number']);
            $table->dropColumn('return_number');
        });
    }
};
