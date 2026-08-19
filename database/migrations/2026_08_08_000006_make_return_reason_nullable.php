<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Penjelasan bebas tidak lagi selalu wajib.
 *
 * Alasan yang dipilih dari daftar sudah cukup menjelaskan duduk perkaranya —
 * memaksa pembeli mengetik minimal 15 karakter untuk "Ukuran tidak pas" hanya
 * menghasilkan isian asal-asalan. Penjelasan tetap wajib bila pembeli memilih
 * "Alasan lain", karena di situ kodenya sendiri tidak memberi tahu apa pun.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `order_returns` MODIFY `reason` TEXT NULL');
    }

    public function down(): void
    {
        // Baris tanpa penjelasan diisi penanda agar kolomnya bisa dikembalikan
        // menjadi wajib tanpa menggagalkan migrasi.
        DB::table('order_returns')->whereNull('reason')->update(['reason' => '-']);

        DB::statement('ALTER TABLE `order_returns` MODIFY `reason` TEXT NOT NULL');
    }
};
