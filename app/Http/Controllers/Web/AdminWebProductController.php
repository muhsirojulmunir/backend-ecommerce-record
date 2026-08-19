<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Admin\ProductService;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminWebProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $products = $this->productService->getAllProducts($request->all());
        return view('admin.products', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.products-form', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProductRequest($request);

        // Process slot-based image uploads (new_images[0..4])
        $imagePaths = $this->processSlottedImages($request);
        if (!empty($imagePaths)) {
            $validated['image']  = $imagePaths[0]; // Cover = first image
            $validated['images'] = $imagePaths;
        }

        try {
            $product = $this->productService->createProduct($validated);
            $this->simpanFotoWarna($request, $product);

            return redirect()->route('admin.products')->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan produk: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $product    = $this->productService->getProductById($id);
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        // Foto yang sudah tersimpan per warna, dipakai form untuk pratinjau
        $fotoWarna = $product->images()
            ->whereNotNull('color')
            ->get()
            ->mapWithKeys(fn ($img) => [$img->color => $img->image_url])
            ->all();

        return view('admin.products-form', compact('product', 'categories', 'fotoWarna'));
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateProductRequest($request);
        $product   = $this->productService->getProductById($id);

        // ── Image logic: merge slot-uploaded files with existing images ──────
        //  Existing images indexed by slot (from hidden fields: existing_images[i])
        $existingBySlot = $request->input('existing_images', []);   // ['0' => 'products/xxx.jpg', ...]

        $finalPaths = [];
        for ($i = 0; $i < 5; $i++) {
            if ($request->hasFile("new_images.$i")) {
                // New upload for this slot → store, delete old if existed here
                $oldPath = $existingBySlot[$i] ?? null;
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
                $finalPaths[] = $request->file("new_images.$i")->store('products', 'public');
            } elseif (!empty($existingBySlot[$i])) {
                // No new file → keep old image
                $finalPaths[] = $existingBySlot[$i];
            }
            // Slot is empty (no new file + no existing) → skip
        }

        // Delete old images that are no longer in the final list.
        // Foto khusus warna dilewati agar tidak ikut terhapus — pengelolaannya
        // ada di simpanFotoWarna().
        foreach ($product->images->whereNull('color') as $oldImg) {
            if (!in_array($oldImg->image_path, $finalPaths)) {
                Storage::disk('public')->delete($oldImg->image_path);
            }
        }

        if (!empty($finalPaths)) {
            $validated['image']  = $finalPaths[0]; // Cover = first image
            $validated['images'] = $finalPaths;
        }

        try {
            $this->productService->updateProduct($id, $validated);
            $this->simpanFotoWarna($request, $this->productService->getProductById($id));

            return redirect()->route('admin.products')->with('success', 'Produk berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui produk: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    /**
     * Menandai atau melepas produk dari "Our Collection" di halaman utama.
     *
     * Disediakan sebagai sakelar satu klik di daftar produk, bukan hanya
     * lewat form ubah produk — mengatur etalase berarti membanding-bandingkan
     * banyak produk sekaligus, dan membuka satu per satu terlalu memakan waktu.
     */
    /**
     * Mengubah stok satu varian langsung dari daftar produk.
     *
     * Ada supaya stok yang habis bisa dikosongkan tanpa membuka halaman ubah
     * produk satu per satu — pekerjaan yang paling sering dilakukan dan paling
     * membosankan kalau harus lewat borang penuh.
     *
     * Sengaja HANYA menyentuh kolom stok. Harga, ukuran, warna, dan SKU tidak
     * bisa diubah dari sini; jalur cepat yang bisa mengubah segalanya justru
     * mengundang kekeliruan yang tidak disadari.
     */
    public function updateVariantStock(Request $request, $id)
    {
        $varian = \App\Models\ProductVariant::findOrFail($id);

        $data = $request->validate([
            'stock' => ['required', 'integer', 'min:0', 'max:999999'],
        ], [
            'stock.required' => 'Jumlah stok wajib diisi.',
            'stock.integer'  => 'Stok harus berupa angka bulat.',
            'stock.min'      => 'Stok tidak boleh kurang dari 0.',
            'stock.max'      => 'Stok terlalu besar.',
        ]);

        $sebelum = (int) $varian->stock;
        $varian->stock = (int) $data['stock'];
        $varian->save();

        // Stok induk mengikuti jumlah seluruh variannya, supaya angka di
        // daftar produk tidak bertentangan dengan rincian di bawahnya.
        $produk = $varian->product;
        $totalVarian = (int) $produk->variants()->sum('stock');
        $produk->stock = $totalVarian;
        $produk->save();

        activity('produk')
            ->performedOn($varian)
            ->causedBy(\Illuminate\Support\Facades\Auth::user())
            ->withProperties([
                'produk'   => $produk->name,
                'varian'   => trim(($varian->size ?? '') . ' ' . ($varian->color ?? '')),
                'sku'      => $varian->sku,
                'sebelum'  => $sebelum,
                'sesudah'  => (int) $varian->stock,
            ])
            ->log('mengubah stok varian');

        return response()->json([
            'ok'            => true,
            'produk_id'     => $produk->id,
            'stock'         => (int) $varian->stock,
            'stok_produk'   => $totalVarian,
            'pesan'         => (int) $varian->stock === 0
                ? 'Stok dikosongkan.'
                : 'Stok disimpan.',
        ]);
    }

    public function toggleFeatured($id)
    {
        $produk = \App\Models\Product::findOrFail($id);

        $produk->is_featured = ! $produk->is_featured;
        $produk->save();

        $jumlah = \App\Models\Product::where('is_featured', true)
            ->where('status', 'active')
            ->count();

        return back()->with('success', $produk->is_featured
            ? 'Produk masuk Our Collection. Sekarang ada ' . $jumlah . ' produk di sana.'
            : 'Produk dikeluarkan dari Our Collection. Sisa ' . $jumlah . ' produk.');
    }

    public function destroy($id)
    {
        try {
            $this->productService->deleteProduct($id);
            return redirect()->route('admin.products')->with('success', 'Produk berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus produk: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function validateProductRequest(Request $request): array
    {
        return $request->validate([
            'name'                      => 'required|string|max:255',
            'category_id'               => 'required|exists:categories,id',
            'description'               => 'required|string',
            'material'                  => 'nullable|string|max:255',
            'price'                     => 'required|numeric|min:0',
            'original_price'            => 'nullable|numeric|min:0',
            'stock'                     => 'required|integer|min:0',
            'status'                    => 'required|in:active,inactive,out_of_stock',
            // Per-slot images
            'new_images.0'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'new_images.1'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'new_images.2'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'new_images.3'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'new_images.4'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            // Foto per warna. Berkas besar sudah diperkecil otomatis oleh
            // middleware KompresUnggahanGambar sebelum sampai di sini.
            'color_images.*'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'color_image_colors.*'      => 'nullable|string|max:100',
            'weight_gram'               => 'nullable|integer|min:0',
            'package_length'            => 'nullable|numeric|min:0',
            'package_width'             => 'nullable|numeric|min:0',
            'package_height'            => 'nullable|numeric|min:0',
            'courier_providers'         => 'nullable|array',
            'variants'                  => 'nullable|array',
            'variants.*.color'          => 'required|string|max:100',
            'variants.*.color_hex'      => 'nullable|string|max:7',
            'variants.*.size'           => 'required|string|max:50',
            'variants.*.stock'          => 'required|integer|min:0',
            'variants.*.price_adjustment' => 'required|numeric',
            'variants.*.sku'            => 'nullable|string|max:100',
        ]);
    }

    /**
     * Simpan foto khusus tiap warna varian.
     *
     * Satu warna hanya menyimpan satu foto; mengunggah foto baru akan
     * menggantikan yang lama, termasuk menghapus berkas lamanya.
     * Warna yang tidak diberi foto akan memakai foto utama produk.
     */
    private function simpanFotoWarna(Request $request, $product): void
    {
        $warna  = $request->input('color_image_colors', []);
        $hapus  = $request->input('color_image_remove', []);
        $berkas = $request->file('color_images', []);

        foreach ($warna as $i => $namaWarna) {
            $namaWarna = trim((string) $namaWarna);

            if ($namaWarna === '') {
                continue;
            }

            $mintaHapus = ! empty($hapus[$i]);
            $adaBerkas  = isset($berkas[$i]) && $berkas[$i]->isValid();

            if (! $mintaHapus && ! $adaBerkas) {
                continue;   // warna ini tidak diubah
            }

            // Buang foto lama untuk warna ini beserta berkasnya
            $lama = $product->images()->where('color', $namaWarna)->get();
            foreach ($lama as $img) {
                Storage::disk('public')->delete($img->image_path);
                $img->delete();
            }

            if ($adaBerkas) {
                $product->images()->create([
                    'image_path' => $berkas[$i]->store('products', 'public'),
                    'color'      => $namaWarna,
                    'sort_order' => 100 + (int) $i,   // ditempatkan setelah galeri umum
                ]);
            }
        }
    }

    /**
     * Untuk create(): proses new_images[0..4] saja (tidak ada existing).
     */
    private function processSlottedImages(Request $request): array
    {
        $paths = [];
        for ($i = 0; $i < 5; $i++) {
            if ($request->hasFile("new_images.$i")) {
                $paths[] = $request->file("new_images.$i")->store('products', 'public');
            }
        }
        return $paths;
    }
}
