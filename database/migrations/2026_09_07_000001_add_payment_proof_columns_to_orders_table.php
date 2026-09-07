<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_proof')) {
                $table->string('payment_proof')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'payment_proof_uploaded_at')) {
                $table->timestamp('payment_proof_uploaded_at')->nullable()->after('payment_proof');
            }
            if (!Schema::hasColumn('orders', 'payment_rejection_note')) {
                $table->text('payment_rejection_note')->nullable()->after('payment_proof_uploaded_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('orders', 'payment_proof')) {
                $columns[] = 'payment_proof';
            }
            if (Schema::hasColumn('orders', 'payment_proof_uploaded_at')) {
                $columns[] = 'payment_proof_uploaded_at';
            }
            if (Schema::hasColumn('orders', 'payment_rejection_note')) {
                $columns[] = 'payment_rejection_note';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
