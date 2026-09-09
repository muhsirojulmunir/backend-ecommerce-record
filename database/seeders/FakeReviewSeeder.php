<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * Seeder ulasan fiktif.
 *
 * Membuat akun pembeli palsu, pesanan selesai (completed & paid),
 * baris pesanan, dan ulasan produk agar seluruh FK constraint
 * (user_id, order_id, order_item_id, product_id) valid dan natural.
 */
class FakeReviewSeeder extends Seeder
{
    private array $buyers = [
        ['name' => 'Muhammad Rizal',       'email' => 'muhammad.rizal.fake01@mail.test'],
        ['name' => 'Budi Santoso',         'email' => 'budi.santoso.fake02@mail.test'],
        ['name' => 'Rizky Aditya',         'email' => 'rizky.aditya.fake03@mail.test'],
        ['name' => 'Dewi Puspita',         'email' => 'dewi.puspita.fake04@mail.test'],
        ['name' => 'Dimas Kurniawan',      'email' => 'dimas.kurniawan.fake05@mail.test'],
        ['name' => 'Fikri Hidayat',        'email' => 'fikri.hidayat.fake06@mail.test'],
        ['name' => 'Anisa Rahmawati',      'email' => 'anisa.rahmawati.fake07@mail.test'],
        ['name' => 'Aditya Nugroho',       'email' => 'aditya.nugroho.fake08@mail.test'],
        ['name' => 'Bagus Prasetyo',       'email' => 'bagus.prasetyo.fake09@mail.test'],
        ['name' => 'Siti Mardiyah',        'email' => 'siti.mardiyah.fake10@mail.test'],
        ['name' => 'Hendra Wijaya',        'email' => 'hendra.wijaya.fake11@mail.test'],
        ['name' => 'Maya Setiawati',       'email' => 'maya.setiawati.fake12@mail.test'],
        ['name' => 'Farhan Maulana',       'email' => 'farhan.maulana.fake13@mail.test'],
        ['name' => 'Nabila Kusuma',        'email' => 'nabila.kusuma.fake14@mail.test'],
        ['name' => 'Rian Zulfikar',        'email' => 'rian.zulfikar.fake15@mail.test'],
        ['name' => 'Gilang Permana',       'email' => 'gilang.permana.fake16@mail.test'],
        ['name' => 'Putri Anggraeni',      'email' => 'putri.anggraeni.fake17@mail.test'],
        ['name' => 'Ahmad Taufiq',         'email' => 'ahmad.taufiq.fake18@mail.test'],
        ['name' => 'Lestari Sari',         'email' => 'lestari.sari.fake19@mail.test'],
        ['name' => 'Eko Prasetyo',         'email' => 'eko.prasetyo.fake20@mail.test'],
    ];

    private array $comments5 = [
        'Barang sampai dengan aman. Sepatunya enteng banget dan empuk dipakai jalan jauh.',
        'Kualitasnya mantap, jahitan rapi, lem-leman juga bersih. Ukurannya pas di kaki.',
        'Pengiriman kilat, sehari sampai. Produk sesuai gambar dan deskripsi.',
        'Bagus banget untuk harga segini, bahannya tebal dan solnya kesat gak licin.',
        'Sudah pembelian kedua di toko ini, selalu memuaskan pelayanannya.',
        'Nyaman banget dipakai seharian kerja/sekolah, kakiku gak lecet.',
        'Mantap jiwa, packaging rapi kardus tidak penyok. Recommended seller!',
        'Desainnya keren dan modern. Pas dicoba langsung nyaman di kaki.',
        'Beli buat anak, anaknya seneng banget langsung dipakai. Ukuran pas sesuai deskripsi.',
        'Keren sepatunya, enteng banget serasa ga pakai sepatu. Makasih seller!',
        'Packing aman, barang ori dan berkualitas. Bakal langganan nih.',
        'Sepatunya bagus sesuai ekspektasi. Warna dan modelnya cakep.',
        'Modelnya keren, sol kuat dan kokoh. Nyaman banget dibuat aktivitas luar.',
        'Sesuai pesanan, pengiriman cepat dan respon toko baik.',
        'Pas di kaki, solnya empuk. Enak buat jalan santai ataupun aktivitas harian.',
        'Top markotop, kualitas bintang lima harga terjangkau.',
        null,
        null,
        null,
        null,
        null,
    ];

    private array $comments4 = [
        'Barangnya oke bagus, cuma agak lama di ekspedisinya. Tapi sepatunya mantul.',
        'Sepatunya bagus dan nyaman, kardus agak penyok dikit di perjalanan tapi dalemnya aman.',
        'Kualitas oke sesuai harga, ukuran agak ngepas dikit saran naik 1 size kalau kaki lebar.',
        'Bagus, bahan lentur dan sol empuk. Semoga awet dipakai harian.',
        null,
        null,
    ];

    private array $cities = [
        'Surabaya', 'Jakarta', 'Bandung', 'Semarang', 'Malang',
        'Yogyakarta', 'Sidoarjo', 'Medan', 'Tangerang', 'Bekasi',
        'Depok', 'Bogor', 'Solo', 'Denpasar', 'Makassar',
        'Palembang', 'Gresik', 'Kediri', 'Cirebon', 'Jember'
    ];

    private array $streets = [
        'Mawar', 'Melati', 'Sudirman', 'Diponegoro', 'Gajah Mada',
        'Pahlawan', 'Ahmad Yani', 'Pemuda', 'Merdeka', 'Kenanga'
    ];

    public function run(): void
    {
        $productIds = DB::table('products')->where('status', 'active')->pluck('id')->toArray();
        if (empty($productIds)) {
            $this->command->warn('Tidak ada produk aktif.');
            return;
        }

        $now = now();

        // 1. Bersihkan data ulasan fiktif sebelumnya agar idempoten
        $fakeUserEmails = array_column($this->buyers, 'email');
        $existingFakeUserIds = DB::table('users')->whereIn('email', $fakeUserEmails)->pluck('id');
        if ($existingFakeUserIds->isNotEmpty()) {
            DB::table('product_reviews')->whereIn('user_id', $existingFakeUserIds)->delete();
            $oldOrderIds = DB::table('orders')->whereIn('user_id', $existingFakeUserIds)->pluck('id');
            if ($oldOrderIds->isNotEmpty()) {
                DB::table('order_items')->whereIn('order_id', $oldOrderIds)->delete();
                DB::table('orders')->whereIn('id', $oldOrderIds)->delete();
            }
        }

        // 2. Buat / pastikan user fiktif ada
        $users = [];
        foreach ($this->buyers as $b) {
            $user = DB::table('users')->where('email', $b['email'])->first();
            if (!$user) {
                $id = DB::table('users')->insertGetId([
                    'name'              => $b['name'],
                    'email'             => $b['email'],
                    'password'          => Hash::make('fake-review-pw-123!'),
                    'role'              => 'customer',
                    'email_verified_at' => $now,
                    'created_at'        => $now->copy()->subDays(rand(60, 180)),
                    'updated_at'        => $now,
                ]);
                $users[] = ['id' => $id, 'name' => $b['name']];
            } else {
                $users[] = ['id' => $user->id, 'name' => $user->name];
            }
        }

        $variantsByProduct = DB::table('product_variants')
            ->whereIn('product_id', $productIds)
            ->get()
            ->groupBy('product_id');

        $products = DB::table('products')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $paymentMethods = ['BCA', 'BNI', 'BRI', 'MANDIRI', 'QRIS'];
        $created = 0;

        // 3. Buat 4-6 ulasan per produk aktif
        foreach ($productIds as $productId) {
            $product = $products[$productId] ?? null;
            if (!$product) continue;

            $variants = $variantsByProduct[$productId] ?? collect();
            $reviewCount = rand(4, 6);

            for ($i = 0; $i < $reviewCount; $i++) {
                $user = $users[array_rand($users)];

                // Rating acak: 80% bintang 5, 20% bintang 4
                $rating = (rand(1, 10) <= 8) ? 5 : 4;
                $commentPool = ($rating === 5) ? $this->comments5 : $this->comments4;
                $comment = $commentPool[array_rand($commentPool)];

                // Tanggal acak dalam rentang 5 sampai 120 hari lalu
                $daysAgo = rand(5, 120);
                $reviewDate = $now->copy()
                    ->subDays($daysAgo)
                    ->subHours(rand(0, 23))
                    ->subMinutes(rand(1, 59));

                $orderCreatedDate = $reviewDate->copy()->subDays(rand(3, 7))->subHours(rand(1, 12));

                // Pilih varian jika ada
                $variant = $variants->isNotEmpty()
                    ? $variants->values()->get(rand(0, $variants->count() - 1))
                    : null;

                $variantInfo = $variant
                    ? 'Size ' . ($variant->size ?? '-') . ($variant->color ? ' - ' . $variant->color : '')
                    : null;

                $variantPrice = (float) $product->price + (float) ($variant->price_adjustment ?? 0);
                $city = $this->cities[array_rand($this->cities)];
                $street = $this->streets[array_rand($this->streets)];

                // Pesanan fiktif (completed & paid)
                $orderId = DB::table('orders')->insertGetId([
                    'user_id'          => $user['id'],
                    'order_number'     => 'ORD-' . $orderCreatedDate->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                    'status'           => 'completed',
                    'payment_status'   => 'paid',
                    'payment_method'   => $paymentMethods[array_rand($paymentMethods)],
                    'total_price'      => $variantPrice,
                    'shipping_cost'    => 15000,
                    'grand_total'      => $variantPrice + 15000,
                    'is_fake'          => true,
                    'shipping_address' => json_encode([
                        'name'        => $user['name'],
                        'phone'       => '08' . rand(12, 98) . rand(1000000, 9999999),
                        'address'     => 'Jl. ' . $street . ' No. ' . rand(1, 120),
                        'city'        => $city,
                        'province'    => 'Jawa Timur',
                        'postal_code' => (string) rand(60111, 69999),
                    ]),
                    'courier'          => 'J&T Express',
                    'courier_code'     => 'jnt',
                    'tracking_number'  => 'JP' . rand(1000000000, 9999999999),
                    'completed_at'     => $reviewDate->copy()->subHours(rand(2, 24)),
                    'delivered_at'     => $reviewDate->copy()->subHours(rand(4, 36)),
                    'created_at'       => $orderCreatedDate,
                    'updated_at'       => $reviewDate,
                ]);

                // Item pesanan
                $orderItemId = DB::table('order_items')->insertGetId([
                    'order_id'           => $orderId,
                    'product_id'         => $productId,
                    'product_variant_id' => $variant?->id,
                    'product_name'       => $product->name,
                    'variant_info'       => $variantInfo,
                    'quantity'           => 1,
                    'price'              => $variantPrice,
                    'created_at'         => $orderCreatedDate,
                    'updated_at'         => $reviewDate,
                ]);

                // Ulasan produk
                DB::table('product_reviews')->insert([
                    'product_id'    => $productId,
                    'order_id'      => $orderId,
                    'order_item_id' => $orderItemId,
                    'user_id'       => $user['id'],
                    'rating'        => $rating,
                    'comment'       => $comment,
                    'photos'        => null,
                    'is_hidden'     => false,
                    'created_at'    => $reviewDate,
                    'updated_at'    => $reviewDate,
                ]);

                $created++;
            }
        }

        $this->command->info("Selesai: {$created} ulasan fiktif berhasil dibuat untuk " . count($productIds) . " produk aktif.");
    }
}
