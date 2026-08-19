<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Melengkapi pengajuan pengembalian barang.
 *
 * Tabel order_returns sebelumnya hanya menyimpan satu kolom "reason" berupa
 * teks bebas. Sekarang alasan terpilih dan penjelasan pembeli dipisah, dan
 * ditambahkan bentuk penyelesaian yang diminta: tukar ukuran atau pengembalian
 * dana ke R_Pay.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            // Alasan yang dipilih dari daftar (kode dari config/alasan-retur.php)
            $table->string('reason_code')->nullable()->after('type');

            // Bentuk penyelesaian yang diminta pembeli
            $table->enum('resolution', ['refund', 'exchange'])->nullable()->after('reason');

            // Ukuran pengganti bila memilih tukar ukuran
            $table->string('exchange_size')->nullable()->after('resolution');

            // Nominal yang dikembalikan bila disetujui sebagai pengembalian dana
            $table->decimal('refund_amount', 14, 2)->nullable()->after('exchange_size');

            $table->foreignId('decided_by')->nullable()->after('admin_notes')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('decided_by');
            $table->dropColumn(['reason_code', 'resolution', 'exchange_size', 'refund_amount']);
        });
    }
};
