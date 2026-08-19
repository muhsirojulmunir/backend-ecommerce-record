<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * R_Pay — dompet digital pembeli.
 *
 * Saldo sengaja TIDAK disimpan sebagai satu angka yang ditimpa berulang kali.
 * Setiap pergerakan dana dicatat sebagai satu baris di buku besar
 * (rpay_transactions) berikut saldo sesudahnya, sehingga:
 *
 *   - riwayatnya bisa ditelusuri baris demi baris saat ada selisih,
 *   - saldo bisa dihitung ulang kapan pun dari nol,
 *   - kesalahan tulis tidak menghapus jejak dana sebelumnya.
 *
 * Kolom users.rpay_balance hanyalah salinan cepat untuk ditampilkan dan
 * diurutkan; sumber kebenarannya tetap buku besar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('rpay_balance', 14, 2)->default(0)->after('is_blocked');
        });

        Schema::create('rpay_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // credit = saldo masuk, debit = saldo keluar
            $table->enum('direction', ['credit', 'debit']);

            // Selalu positif; arahnya ditentukan kolom direction supaya tidak
            // ada kebingungan tanda minus saat dijumlahkan.
            $table->decimal('amount', 14, 2);

            // Saldo sesudah baris ini dibukukan — memudahkan penelusuran
            // tanpa harus menjumlahkan ulang seluruh riwayat.
            $table->decimal('balance_after', 14, 2);

            // Dari mana dana ini berasal atau ke mana perginya.
            $table->enum('source', [
                'refund',       // pengembalian dana pesanan
                'checkout',     // dipakai membayar pesanan
                'withdrawal',   // dicairkan ke rekening bank
                'reversal',     // pengembalian karena pesanan batal
                'adjustment',   // penyesuaian manual oleh admin
            ]);

            // Kaitan ke sumbernya (order, penarikan, dsb) — sengaja tidak
            // memakai foreign key agar riwayat dana tidak ikut terhapus
            // bila data sumbernya dihapus.
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->string('description');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('rpay_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount', 14, 2);

            // Rekening tujuan disimpan sebagai salinan pada saat pengajuan,
            // supaya bukti pencairan tetap utuh meski pembeli kelak
            // mengganti data rekeningnya.
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('account_holder');

            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');

            // Perkiraan dana sampai, dihitung 1-2 hari kerja sejak pengajuan.
            $table->date('estimated_ready_at')->nullable();

            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rpay_withdrawals');
        Schema::dropIfExists('rpay_transactions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rpay_balance');
        });
    }
};
