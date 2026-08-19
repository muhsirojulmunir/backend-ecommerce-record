<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Support\Spreadsheet\XlsxReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Membaca berkas Excel/CSV berisi banyak produk, memvalidasi tiap baris, lalu menyimpannya sekaligus.
 */
class ProductImportService
{
    /** Batas jumlah baris agar impor tidak menggantung terlalu lama. */
    public const MAX_ROWS = 500;

    /** Status produk yang diterima, beserta padanan Bahasa Indonesia. */
    private const STATUS_MAP = [
        'active'       => 'active',
        'aktif'        => 'active',
        'inactive'     => 'inactive',
        'nonaktif'     => 'inactive',
        'tidak aktif'  => 'inactive',
        'out_of_stock' => 'out_of_stock',
        'habis'        => 'out_of_stock',
        'stok habis'   => 'out_of_stock',
    ];

    private const TRUE_WORDS = ['ya', 'yes', 'y', '1', 'true', 'benar'];

    /** Kurir bawaan untuk semua produk hasil impor. */
    public const DEFAULT_COURIERS = ['jne', 'jnt', 'sicepat'];

    /**
     * Definisi kolom template.
     * kunci => [judul, wajib?, keterangan, contoh]
     */
    public const COLUMNS = [
        'nama_produk'  => ['Nama Produk',        true,  'Nama produk yang tampil ke pembeli.',                              'Sepatu Sekolah Hitam Anak'],
        'kategori'     => ['Kategori',           true,  'Kalau kategori belum ada, sistem membuatnya otomatis.',            'Sepatu Sekolah'],
        'deskripsi'    => ['Deskripsi',          true,  'Penjelasan produk.',                                               'Sepatu sekolah bahan kanvas, jahitan kuat, nyaman dipakai seharian.'],
        'harga'        => ['Harga Jual',         true,  'Angka saja, tanpa titik atau "Rp". Contoh: 150000',                '150000'],
        'harga_coret'  => ['Harga Coret',        false, 'Harga sebelum diskon. Kosongkan kalau tidak ada.',                 '200000'],
        'stok'         => ['Stok',               true,  'Jumlah stok total. Angka bulat.',                                  '50'],
        'status'       => ['Status',             false, 'aktif / nonaktif / habis. Kosong dianggap aktif.',                 'aktif'],
        'bahan'        => ['Bahan',              false, 'Material produk.',                                                 'Kanvas'],
        'berat_gram'   => ['Berat (gram)',       false, 'Berat untuk hitung ongkir.',                                       '600'],
        'panjang_cm'   => ['Panjang (cm)',       false, 'Panjang paket.',                                                   '30'],
        'lebar_cm'     => ['Lebar (cm)',         false, 'Lebar paket.',                                                     '20'],
        'tinggi_cm'    => ['Tinggi (cm)',        false, 'Tinggi paket.',                                                    '12'],
        'unggulan'     => ['Produk Unggulan',    false, 'ya / tidak. Kosong dianggap tidak.',                               'tidak'],
        // Kolom varian dipecah jadi baris-baris terpisah di sheet "Varian",
        // supaya tiap ukuran punya barisnya sendiri untuk diisi SKU.
    ];

    /**
     * Kolom pada sheet kedua ("Varian"). Satu baris = satu kombinasi
     * warna + ukuran, lengkap dengan SKU-nya masing-masing.
     */
    public const VARIANT_COLUMNS = [
        'nama_produk'   => ['Nama Produk',    true,  'INI PENGHUBUNGNYA. Pilih dari dropdown (isinya diambil dari sheet Produk). Baris varian akan masuk ke produk yang namanya sama persis. Untuk 1 produk dengan 3 ukuran, tulis nama yang sama di 3 baris.', 'Sepatu Sekolah Hitam Anak'],
        'warna'         => ['Warna',          true,  'Nama warna varian.',                             'Hitam'],
        'ukuran'        => ['Ukuran',         true,  'Ukuran varian. Satu baris untuk satu ukuran.',   '38'],
        'stok'          => ['Stok',           true,  'Stok untuk kombinasi warna + ukuran ini saja.',  '10'],
        'selisih_harga' => ['Selisih Harga',  false, 'Ditambahkan ke harga jual. Isi 0 kalau sama.',   '0'],
        'sku'           => ['SKU',            false, 'Kode unik per ukuran. Kosong = dibuat otomatis.', 'REC-SSH-38'],
    ];

    /**
 * Baca berkas dan kembalikan hasil parsing untuk pratinjau.
 *
 * @return array{rows: array, summary: array, newCategories: array, headerError: ?string}
 */
    public function parse(string $absolutePath, string $duplicateMode = 'skip'): array
    {
        $sheets = XlsxReader::readAll($absolutePath);

        $table = XlsxReader::sheet($sheets, 'Produk');

        // Berkas CSV atau .xlsx tanpa sheet bernama "Produk" → pakai sheet pertama
        if (empty($table)) {
            $table = $sheets === [] ? [] : reset($sheets);
        }

        // Varian dibaca dari sheet terpisah supaya tiap ukuran punya baris SKU sendiri
        $variantsByProduct = $this->parseVariantSheet(XlsxReader::sheet($sheets, 'Varian'));

        if (empty($table)) {
            return $this->emptyResult('Berkas kosong atau tidak bisa dibaca.');
        }

        $headerRow = array_shift($table);
        $map = $this->mapHeader($headerRow);

        $missing = collect(self::COLUMNS)
            ->filter(fn ($def) => $def[1])
            ->keys()
            ->reject(fn ($key) => isset($map[$key]))
            ->all();

        if (! empty($missing)) {
            $labels = collect($missing)->map(fn ($k) => self::COLUMNS[$k][0])->implode(', ');

            return $this->emptyResult(
                "Kolom wajib tidak ditemukan di baris judul: {$labels}. " .
                'Pastikan memakai template yang disediakan dan judul kolom tidak diubah.'
            );
        }

        // Kategori & nama produk yang sudah ada, dipakai untuk deteksi duplikat
        $existingCategories = Category::pluck('id', 'slug');
        $existingProducts   = Product::pluck('id', 'name')->mapWithKeys(
            fn ($id, $name) => [Str::lower(trim($name)) => $id]
        );

        $rows          = [];
        $newCategories = [];
        $seenNames     = [];
        $rowNumber     = 1; // baris 1 adalah judul

        foreach ($table as $raw) {
            $rowNumber++;

            if ($this->isBlankRow($raw) || $this->isGuideRow($raw)) {
                continue;
            }

            if (count($rows) >= self::MAX_ROWS) {
                break;
            }

            $rows[] = $this->parseRow(
                $raw,
                $map,
                $rowNumber,
                $existingCategories,
                $existingProducts,
                $newCategories,
                $seenNames,
                $duplicateMode,
                $variantsByProduct
            );
        }

        // Varian yang namanya tidak cocok dengan produk mana pun perlu diberitahukan
        $orphanVariants = $this->findOrphanVariants($variantsByProduct, $rows);

        return [
            'rows'           => $rows,
            'newCategories'  => array_values($newCategories),
            'orphanVariants' => $orphanVariants,
            'headerError'    => null,
            'summary'        => $this->summarize($rows, $newCategories),
        ];
    }

    /**
     * Simpan hasil parsing ke database.
     * Hanya baris berstatus "ok" atau "update" yang diproses.
     */
    public function import(array $parsed, string $duplicateMode = 'skip'): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $failed  = [];
        $categoriesCreated = [];

        foreach ($parsed['rows'] as $row) {
            if ($row['status'] === 'error') {
                $failed[] = ['line' => $row['line'], 'name' => $row['data']['name'] ?? '-', 'reason' => implode(' ', $row['errors'])];
                continue;
            }

            if ($row['status'] === 'skip') {
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($row, $duplicateMode, &$created, &$updated, &$categoriesCreated) {
                    $category = $this->resolveCategory($row['categoryName'], $categoriesCreated);

                    $payload = $row['data'];
                    $payload['category_id'] = $category->id;

                    $existing = $row['existingId'] ? Product::find($row['existingId']) : null;

                    if ($existing && $duplicateMode === 'update') {
                        $this->applyProduct($existing, $payload, $row['variants'], $row['shipping'], false);
                        $updated++;
                    } else {
                        $product = new Product();
                        $this->applyProduct($product, $payload, $row['variants'], $row['shipping'], true);
                        $created++;
                    }
                });
            } catch (\Throwable $e) {
                $failed[] = [
                    'line'   => $row['line'],
                    'name'   => $row['data']['name'] ?? '-',
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return [
            'created'           => $created,
            'updated'           => $updated,
            'skipped'           => $skipped,
            'failed'            => $failed,
            'categoriesCreated' => array_values($categoriesCreated),
        ];
    }

    // ─── Pembacaan berkas ─────────────────────────────────────────────────────

    /**
     * Baca sheet pertama menjadi array dua dimensi.
     */
    private function readSheet(string $path): array
    {
        return XlsxReader::read($path);
    }

    /**
 * Cocokkan judul kolom di berkas dengan kunci kolom template.
 *
 * @return array<string, int> kunci kolom => indeks kolom
 */
    private function mapHeader(array $headerRow): array
    {
        return $this->mapHeaderFor($headerRow, self::COLUMNS);
    }

    /**
 * Versi umum mapHeader() untuk definisi kolom mana pun.
 *
 * @return array<string, int>
 */
    private function mapHeaderFor(array $headerRow, array $definition): array
    {
        $map = [];

        foreach ($headerRow as $index => $cell) {
            $normalized = $this->normalizeHeader((string) $cell);

            if ($normalized === '') {
                continue;
            }

            foreach ($definition as $key => [$label]) {
                if (isset($map[$key])) {
                    continue;
                }

                if ($normalized === $this->normalizeHeader($label) || $normalized === $this->normalizeHeader($key)) {
                    $map[$key] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    private function normalizeHeader(string $value): string
    {
        $value = Str::lower(trim($value));
        $value = str_replace('*', '', $value);
        $value = preg_replace('/\(.*?\)/', '', $value);   // buang "(gram)", "(cm)"
        $value = preg_replace('/[^a-z0-9]/', '', $value); // buang spasi, underscore, dsb

        return $value ?? '';
    }

    private function isBlankRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Baris petunjuk & contoh bawaan template ditandai di kolom pertama,
     * jadi tetap dilewati walau admin lupa menghapusnya.
     */
    private function isGuideRow(array $row): bool
    {
        $first = Str::upper(ltrim((string) ($row[0] ?? '')));

        return str_starts_with($first, ProductTemplateService::HINT_MARK)
            || str_starts_with($first, ProductTemplateService::EXAMPLE_MARK);
    }

    // ─── Validasi per baris ───────────────────────────────────────────────────

    private function parseRow(
        array $raw,
        array $map,
        int $line,
        $existingCategories,
        $existingProducts,
        array &$newCategories,
        array &$seenNames,
        string $duplicateMode,
        array $variantsByProduct = []
    ): array {
        $get = fn (string $key) => isset($map[$key]) ? trim((string) ($raw[$map[$key]] ?? '')) : '';

        $errors   = [];
        $warnings = [];

        // ── Nama produk ──
        $name = $get('nama_produk');
        if ($name === '') {
            $errors[] = 'Nama produk wajib diisi.';
        } elseif (mb_strlen($name) > 255) {
            $errors[] = 'Nama produk maksimal 255 karakter.';
        }

        // ── Kategori ──
        $categoryName = $get('kategori');
        $categoryIsNew = false;
        if ($categoryName === '') {
            $errors[] = 'Kategori wajib diisi.';
        } else {
            $slug = Str::slug($categoryName);
            if (! $existingCategories->has($slug) && ! isset($newCategories[$slug])) {
                $newCategories[$slug] = $categoryName;
                $categoryIsNew = true;
            } elseif (isset($newCategories[$slug])) {
                $categoryIsNew = true;
            }
        }

        // ── Deskripsi ──
        $description = $get('deskripsi');
        if ($description === '') {
            $errors[] = 'Deskripsi wajib diisi.';
        }

        // ── Harga ──
        $price = $this->toNumber($get('harga'));
        if ($get('harga') === '') {
            $errors[] = 'Harga wajib diisi.';
        } elseif ($price === null || $price < 0) {
            $errors[] = 'Harga harus berupa angka tidak negatif.';
        }

        $originalPrice = $get('harga_coret') === '' ? null : $this->toNumber($get('harga_coret'));
        if ($get('harga_coret') !== '' && $originalPrice === null) {
            $errors[] = 'Harga coret harus berupa angka.';
        } elseif ($originalPrice !== null && $price !== null && $originalPrice < $price) {
            $warnings[] = 'Harga coret lebih kecil dari harga jual — biasanya harga coret diisi harga sebelum diskon.';
        }

        // ── Stok ──
        $stockRaw = $get('stok');
        $stock = $this->toNumber($stockRaw);
        if ($stockRaw === '') {
            $errors[] = 'Stok wajib diisi.';
        } elseif ($stock === null || $stock < 0) {
            $errors[] = 'Stok harus berupa angka bulat tidak negatif.';
        }

        // ── Status ──
        $statusRaw = Str::lower($get('status'));
        $status = $statusRaw === '' ? 'active' : (self::STATUS_MAP[$statusRaw] ?? null);
        if ($status === null) {
            $errors[] = "Status \"{$get('status')}\" tidak dikenal. Gunakan: aktif, nonaktif, atau habis.";
            $status = 'active';
        }

        // ── Pengiriman ── (kurir memakai daftar default toko)
        $shipping = [
            'weight_gram'       => (int) ($this->toNumber($get('berat_gram')) ?? 0),
            'package_length'    => (float) ($this->toNumber($get('panjang_cm')) ?? 0),
            'package_width'     => (float) ($this->toNumber($get('lebar_cm')) ?? 0),
            'package_height'    => (float) ($this->toNumber($get('tinggi_cm')) ?? 0),
            'courier_providers' => self::DEFAULT_COURIERS,
        ];

        if ($shipping['weight_gram'] <= 0) {
            $warnings[] = 'Berat kosong — ongkir bisa salah hitung. Sebaiknya diisi.';
        }

        // ── Varian dari sheet "Varian", dicocokkan lewat nama produk ──
        $variantKey = Str::lower(trim($name));
        $variantData = $variantsByProduct[$variantKey] ?? ['variants' => [], 'errors' => []];

        $variants = $variantData['variants'];
        $errors   = array_merge($errors, $variantData['errors']);

        $variantStock = array_sum(array_column($variants, 'stock'));
        if (! empty($variants) && $stock !== null && $variantStock !== (int) $stock) {
            $warnings[] = "Total stok varian ({$variantStock}) berbeda dengan kolom Stok (" . (int) $stock . ').';
        }

        // ── Duplikat ──
        $lowerName  = Str::lower($name);
        $existingId = $existingProducts[$lowerName] ?? null;
        $status_    = 'ok';

        if ($name !== '' && isset($seenNames[$lowerName])) {
            $errors[] = "Nama produk ini muncul dua kali di berkas (lihat juga baris {$seenNames[$lowerName]}).";
        } elseif ($name !== '') {
            $seenNames[$lowerName] = $line;
        }

        if ($existingId && empty($errors)) {
            $status_ = $duplicateMode === 'update' ? 'update' : 'skip';
        }

        if (! empty($errors)) {
            $status_ = 'error';
        }

        return [
            'line'         => $line,
            'status'       => $status_,
            'errors'       => $errors,
            'warnings'     => $warnings,
            'categoryName' => $categoryName,
            'categoryIsNew' => $categoryIsNew,
            'existingId'   => $existingId,
            'variants'     => $variants,
            'shipping'     => $shipping,
            'data'         => [
                'name'           => $name,
                'description'    => $description,
                'price'          => $price ?? 0,
                'original_price' => $originalPrice,
                'stock'          => (int) ($stock ?? 0),
                'status'         => $status,
                'is_featured'    => in_array(Str::lower($get('unggulan')), self::TRUE_WORDS, true),
                'material'       => $get('bahan'),
            ],
        ];
    }

    /**
     * Ubah teks angka jadi float. Menerima "150.000", "150000", "Rp 150.000", "1,5".
     */
    private function toNumber(string $value): ?float
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $clean = preg_replace('/[^0-9,.\-]/', '', $value);

        if ($clean === '' || $clean === '-') {
            return null;
        }

        // Titik sebagai pemisah ribuan (150.000) vs desimal (1.5)
        if (str_contains($clean, ',') && str_contains($clean, '.')) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (substr_count($clean, '.') > 1) {
            $clean = str_replace('.', '', $clean);
        } elseif (preg_match('/\.\d{3}$/', $clean)) {
            $clean = str_replace('.', '', $clean);
        } elseif (str_contains($clean, ',')) {
            $clean = str_replace(',', '.', $clean);
        }

        return is_numeric($clean) ? (float) $clean : null;
    }

    /**
 * Baca sheet "Varian" dan kelompokkan per nama produk.
 *
 * @return array<string, array{variants: array, errors: array, lines: array}>
 */
    private function parseVariantSheet(array $table): array
    {
        if (count($table) < 2) {
            return [];
        }

        $headerRow = array_shift($table);
        $map = $this->mapHeaderFor($headerRow, self::VARIANT_COLUMNS);

        // Tanpa kolom kunci, sheet varian tidak bisa dipakai
        foreach (['nama_produk', 'warna', 'ukuran'] as $required) {
            if (! isset($map[$required])) {
                return [];
            }
        }

        $grouped  = [];
        $seenPair = [];
        $seenSku  = [];
        $line     = 1;

        foreach ($table as $raw) {
            $line++;

            if ($this->isBlankRow($raw) || $this->isGuideRow($raw)) {
                continue;
            }

            $get = fn (string $key) => isset($map[$key]) ? trim((string) ($raw[$map[$key]] ?? '')) : '';

            $productName = $get('nama_produk');
            $color       = $get('warna');
            $size        = $get('ukuran');

            if ($productName === '') {
                continue;
            }

            $key = Str::lower($productName);
            $grouped[$key] ??= ['variants' => [], 'errors' => [], 'lines' => []];
            $grouped[$key]['lines'][] = $line;

            if ($color === '' || $size === '') {
                $grouped[$key]['errors'][] = "Sheet Varian baris {$line}: warna dan ukuran wajib diisi.";
                continue;
            }

            $pairKey = $key . '|' . Str::lower($color . '|' . $size);
            if (isset($seenPair[$pairKey])) {
                $grouped[$key]['errors'][] = "Sheet Varian baris {$line}: kombinasi \"{$color} {$size}\" ditulis dua kali untuk produk yang sama.";
                continue;
            }
            $seenPair[$pairKey] = true;

            // SKU harus unik di seluruh berkas maupun terhadap data yang sudah ada
            $sku = $get('sku');
            if ($sku !== '') {
                $skuKey = Str::lower($sku);

                if (isset($seenSku[$skuKey])) {
                    $grouped[$key]['errors'][] = "Sheet Varian baris {$line}: SKU \"{$sku}\" sudah dipakai di baris {$seenSku[$skuKey]}.";
                    continue;
                }

                $seenSku[$skuKey] = $line;
            }

            $grouped[$key]['variants'][] = [
                'color'            => $color,
                'color_hex'        => null,
                'size'             => $size,
                'stock'            => (int) ($this->toNumber($get('stok')) ?? 0),
                'price_adjustment' => (float) ($this->toNumber($get('selisih_harga')) ?? 0),
                'sku'              => $sku !== '' ? $sku : null,
            ];
        }

        return $grouped;
    }

    /**
     * Nama produk di sheet Varian yang tidak punya pasangan di sheet Produk.
     */
    private function findOrphanVariants(array $variantsByProduct, array $rows): array
    {
        $known = collect($rows)
            ->pluck('data.name')
            ->filter()
            ->map(fn ($n) => Str::lower(trim($n)))
            ->flip();

        $orphans = [];

        foreach ($variantsByProduct as $key => $group) {
            if (! $known->has($key)) {
                $orphans[] = [
                    'name'  => $key,
                    'count' => count($group['variants']),
                    'lines' => $group['lines'],
                ];
            }
        }

        return $orphans;
    }

    private function summarize(array $rows, array $newCategories): array
    {
        return [
            'total'         => count($rows),
            'ok'            => count(array_filter($rows, fn ($r) => $r['status'] === 'ok')),
            'update'        => count(array_filter($rows, fn ($r) => $r['status'] === 'update')),
            'skip'          => count(array_filter($rows, fn ($r) => $r['status'] === 'skip')),
            'error'         => count(array_filter($rows, fn ($r) => $r['status'] === 'error')),
            'warning'       => count(array_filter($rows, fn ($r) => ! empty($r['warnings']) && $r['status'] !== 'error')),
            'newCategories' => count($newCategories),
        ];
    }

    private function emptyResult(string $message): array
    {
        return [
            'rows'           => [],
            'newCategories'  => [],
            'orphanVariants' => [],
            'headerError'    => $message,
            'summary'       => ['total' => 0, 'ok' => 0, 'update' => 0, 'skip' => 0, 'error' => 0, 'warning' => 0, 'newCategories' => 0],
        ];
    }

    // ─── Penyimpanan ──────────────────────────────────────────────────────────

    /**
     * Ambil kategori berdasarkan nama; buat baru kalau belum ada.
     */
    private function resolveCategory(string $name, array &$categoriesCreated): Category
    {
        $slug = Str::slug($name);

        $category = Category::where('slug', $slug)->first();

        if ($category) {
            return $category;
        }

        $category = Category::create([
            'name'       => $name,
            'slug'       => $slug,
            'is_active'  => true,
            'sort_order' => (int) Category::max('sort_order') + 1,
        ]);

        $categoriesCreated[$slug] = $name;

        return $category;
    }

    private function applyProduct(Product $product, array $payload, array $variants, array $shipping, bool $isNew): void
    {
        $product->fill([
            'name'           => $payload['name'],
            'category_id'    => $payload['category_id'],
            'description'    => $payload['description'],
            'price'          => $payload['price'],
            'original_price' => $payload['original_price'],
            'stock'          => $payload['stock'],
            'status'         => $payload['status'],
            'is_featured'    => $payload['is_featured'],
            'details'        => ['material' => $payload['material'] ?? ''],
        ]);

        if ($isNew) {
            $product->slug  = Str::slug($payload['name']) . '-' . Str::lower(Str::random(5));
            // Produk hasil impor belum punya foto; admin melengkapinya lewat form edit
            $product->image = 'products/default.png';
        }

        $product->save();

        $product->shipping()->updateOrCreate(['product_id' => $product->id], $shipping);

        // Varian ditulis ulang penuh supaya isi berkas jadi sumber kebenaran
        $product->variants()->delete();

        foreach ($variants as $variant) {
            // SKU dari berkas dipakai apa adanya; kalau kosong dibuatkan otomatis
            $variant['sku'] = $this->resolveSku($product, $variant);
            $product->variants()->create($variant);
        }
    }

    /**
     * Pakai SKU yang diisi admin, atau buatkan yang unik kalau dikosongkan.
     * Kolom sku punya unique index, jadi bentrokan harus dihindari.
     */
    private function resolveSku(Product $product, array $variant): string
    {
        $given = trim((string) ($variant['sku'] ?? ''));

        if ($given !== '') {
            // Bentrok dengan varian produk lain → beri akhiran pembeda
            $taken = \App\Models\ProductVariant::where('sku', $given)
                ->where('product_id', '!=', $product->id)
                ->exists();

            return $taken ? $given . '-' . Str::upper(Str::random(3)) : $given;
        }

        $base = Str::upper(Str::substr(Str::slug($product->name, ''), 0, 6));
        $tail = Str::upper(Str::slug($variant['color'] . $variant['size'], ''));

        return "{$base}-{$tail}-" . Str::upper(Str::random(4));
    }
}
