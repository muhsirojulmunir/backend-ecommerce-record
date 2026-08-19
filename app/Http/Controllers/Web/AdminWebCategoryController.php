<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminWebCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount([
                'products',
                'products as active_products_count' => fn ($q) => $q->where('status', 'active'),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories', [
            'categories' => $categories,
            'stats'      => [
                'total'    => $categories->count(),
                'active'   => $categories->where('is_active', true)->count(),
                'empty'    => $categories->where('products_count', 0)->count(),
                'products' => $categories->sum('products_count'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100', Rule::unique('categories', 'name')],
            'description'      => ['nullable', 'string', 'max:500'],
            'image'            => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:20480'],
            'image_position_x' => ['nullable', 'integer', 'between:0,100'],
            'image_position_y' => ['nullable', 'integer', 'between:0,100'],
            'image_zoom'       => ['nullable', 'numeric', 'between:1,3'],
            'is_active'        => ['nullable'],
        ], [
            'name.unique' => 'Kategori dengan nama ini sudah ada.',
            'image.max'   => 'Ukuran gambar maksimal 20MB.',
        ]);

        $category = new Category([
            'name'        => $data['name'],
            'slug'        => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
            'sort_order'  => (int) Category::max('sort_order') + 1,
        ]);

        if ($request->hasFile('image')) {
            $category->image = $request->file('image')->store('categories', 'public');
        }

        $this->applyImagePosition($category, $data);

        $category->save();

        return redirect()->route('admin.categories')
            ->with('success', "Kategori \"{$category->name}\" berhasil dibuat dan langsung tampil di halaman toko.");
    }

    public function update(Request $request, int $id)
    {
        $category = Category::findOrFail($id);

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'description'      => ['nullable', 'string', 'max:500'],
            'image'            => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:20480'],
            'image_position_x' => ['nullable', 'integer', 'between:0,100'],
            'image_position_y' => ['nullable', 'integer', 'between:0,100'],
            'image_zoom'       => ['nullable', 'numeric', 'between:1,3'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'is_active'        => ['nullable'],
        ], [
            'name.unique' => 'Kategori dengan nama ini sudah ada.',
            'image.max'   => 'Ukuran gambar maksimal 20MB.',
        ]);

        // Slug ikut berubah kalau nama diganti; tautan lama akan mengarah ke slug baru
        if ($data['name'] !== $category->name) {
            $category->slug = $this->uniqueSlug($data['name'], $category->id);
        }

        $category->name        = $data['name'];
        $category->description = $data['description'] ?? null;
        $category->is_active   = $request->boolean('is_active');
        $category->sort_order  = $data['sort_order'] ?? $category->sort_order;

        if ($request->boolean('remove_image')) {
            $this->deleteImage($category->image);
            $category->image = null;
            // Posisi dikembalikan ke tengah supaya gambar berikutnya mulai bersih
            $category->image_position_x = 50;
            $category->image_position_y = 50;
            $category->image_zoom       = 1;
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($category->image);
            $category->image = $request->file('image')->store('categories', 'public');
        }

        if (! $request->boolean('remove_image')) {
            $this->applyImagePosition($category, $data);
        }

        $category->save();

        return redirect()->route('admin.categories')
            ->with('success', "Kategori \"{$category->name}\" berhasil diperbarui.");
    }

    public function toggle(int $id)
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active]);

        $status = $category->is_active
            ? 'ditampilkan kembali di halaman toko'
            : 'disembunyikan dari halaman toko';

        return redirect()->route('admin.categories')
            ->with('success', "Kategori \"{$category->name}\" berhasil {$status}.");
    }

    public function destroy(int $id)
    {
        $category = Category::withCount('products')->findOrFail($id);

        // Produk memakai foreign key cascade — menghapus kategori berisi produk
        // akan ikut menghapus produknya, jadi ditolak di sini.
        if ($category->products_count > 0) {
            return redirect()->route('admin.categories')->with(
                'error',
                "Kategori \"{$category->name}\" masih berisi {$category->products_count} produk. " .
                'Pindahkan produk tersebut ke kategori lain dulu, atau sembunyikan kategori ini saja.'
            );
        }

        $name = $category->name;
        $this->deleteImage($category->image);
        $category->delete();

        return redirect()->route('admin.categories')->with('success', "Kategori \"{$name}\" berhasil dihapus.");
    }

    /**
     * Simpan ulang urutan tampil kategori (dipakai tombol naik/turun).
     */
    public function reorder(Request $request, int $id)
    {
        $data = $request->validate([
            'direction' => ['required', 'in:up,down'],
        ]);

        $category = Category::findOrFail($id);

        $neighbour = Category::query()
            ->when($data['direction'] === 'up',
                fn ($q) => $q->where('sort_order', '<', $category->sort_order)->orderByDesc('sort_order'),
                fn ($q) => $q->where('sort_order', '>', $category->sort_order)->orderBy('sort_order'),
            )
            ->first();

        if (! $neighbour) {
            return redirect()->route('admin.categories');
        }

        // Tukar posisi
        [$category->sort_order, $neighbour->sort_order] = [$neighbour->sort_order, $category->sort_order];
        $category->save();
        $neighbour->save();

        return redirect()->route('admin.categories')->with('success', 'Urutan kategori diperbarui.');
    }

    // ─── Helper privat ────────────────────────────────────────────────────────

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $n    = 2;

        while (Category::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }

    /**
     * Simpan titik fokus & perbesaran gambar hasil geseran admin.
     * Nilai di luar rentang wajar dikembalikan ke posisi tengah.
     */
    private function applyImagePosition(Category $category, array $data): void
    {
        $category->image_position_x = min(100, max(0, (int) ($data['image_position_x'] ?? 50)));
        $category->image_position_y = min(100, max(0, (int) ($data['image_position_y'] ?? 50)));
        $category->image_zoom       = min(3, max(1, (float) ($data['image_zoom'] ?? 1)));
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
