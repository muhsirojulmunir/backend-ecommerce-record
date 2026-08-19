<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Alasan pembatalan yang dipilih pembeli dari daftar pilihan
            $table->string('cancellation_reason')->nullable()->after('notes');
            // Penjelasan tambahan bila pembeli memilih "Alasan lain"
            $table->text('cancellation_note')->nullable()->after('cancellation_reason');
            // Kapan pesanan dibatalkan
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_note');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason', 'cancellation_note', 'cancelled_at']);
        });
    }
};
