<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Pastikan tidak ada stok varian yang bernilai negatif (< 0)
        DB::table('product_variants')
            ->where('stock', '<', 0)
            ->update(['stock' => 0]);

        // 2. Pastikan tidak ada stok produk induk yang bernilai negatif (< 0)
        DB::table('products')
            ->where('stock', '<', 0)
            ->update(['stock' => 0]);

        // 3. Sinkronkan stok produk induk dengan jumlah seluruh variannya
        //    (Mencegah produk ber-varian tertulis 0 atau -1 padahal variannya masih ada stok)
        DB::statement("
            UPDATE products p
            SET p.stock = COALESCE(
                (SELECT SUM(pv.stock) FROM product_variants pv WHERE pv.product_id = p.id),
                0
            )
            WHERE EXISTS (
                SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu rollback perubahan perbaikan data stok
    }
};
