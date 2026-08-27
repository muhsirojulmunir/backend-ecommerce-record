<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminWebBannerController extends Controller
{
    /**
     * Tampilkan daftar banner.
     */
    /**
     * Tampilkan daftar banner.
     */
    public function index(Request $request)
    {
        $banners = Banner::orderBy('sort_order')->orderByDesc('created_at')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $products = \App\Models\Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']);

        return view('admin.banners', compact('banners', 'categories', 'products'));
    }

    /**
     * Aturan berkas banner: batasnya berbeda untuk video dan gambar.
     */
    private function aturanBerkas(bool $wajib): array
    {
        $maksVideo  = (int) config('banner.maks_video_mb', 100);
        $maksGambar = (int) config('banner.maks_gambar_mb', 10);

        return [
            $wajib ? 'required' : 'nullable',
            'file',
            'mimes:jpeg,png,jpg,webp,mp4,mov,webm,ogg',
            function ($atribut, $berkas, $gagal) use ($maksVideo, $maksGambar) {
                if (! $berkas instanceof \Illuminate\Http\UploadedFile || ! $berkas->isValid()) {
                    return;
                }

                $video = str_starts_with((string) $berkas->getMimeType(), 'video/');
                $batasMb = $video ? $maksVideo : $maksGambar;

                if ($berkas->getSize() > $batasMb * 1024 * 1024) {
                    $gagal($video
                        ? 'Video banner maksimal ' . $maksVideo . ' MB. Berkasmu '
                          . number_format($berkas->getSize() / 1048576, 1) . ' MB.'
                        : 'Gambar banner maksimal ' . $maksGambar . ' MB. Berkasmu '
                          . number_format($berkas->getSize() / 1048576, 1) . ' MB.');
                }
            },
        ];
    }

    /**
     * Simpan banner baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'subtitle'   => 'nullable|string|max:500',
            'image'      => $this->aturanBerkas(true),
            'link'       => 'nullable|string|max:500',
            'position'   => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
        ]);

        $position = $request->position ?: 'hero';

        // Validasi maksimal 3 banner hero aktif
        if ($position === 'hero') {
            $count = Banner::where('position', 'hero')->count();
            if ($count >= 3) {
                return redirect()->back()->withErrors(['position' => 'Batas maksimal Banner Utama (Hero) adalah 3 banner. Harap hapus salah satu banner yang sudah ada terlebih dahulu.'])->withInput();
            }
        }

        $imagePath = $request->file('image')->store('banners', 'public');

        Banner::create([
            'title'      => $request->title,
            'subtitle'   => $request->subtitle,
            'image'      => $imagePath,
            'link'       => $request->link,
            'position'   => $position,
            'is_active'  => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? 0,
            'starts_at'  => $request->starts_at ?: null,
            'ends_at'    => $request->ends_at ?: null,
        ]);

        return redirect()->route('admin.banners')->with('success', 'Banner berhasil ditambahkan!');
    }

    /**
     * Update banner yang sudah ada.
     */
    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'subtitle'   => 'nullable|string|max:500',
            'image'      => $this->aturanBerkas(false),
            'link'       => 'nullable|string|max:500',
            'position'   => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
        ]);

        $position = $request->position ?: 'hero';

        // Validasi maksimal 3 banner hero
        if ($position === 'hero') {
            $count = Banner::where('position', 'hero')->where('id', '!=', $banner->id)->count();
            if ($count >= 3) {
                return redirect()->back()->withErrors(['position' => 'Batas maksimal Banner Utama (Hero) adalah 3 banner. Harap hapus salah satu banner terlebih dahulu.'])->withInput();
            }
        }

        $data = [
            'title'      => $request->title,
            'subtitle'   => $request->subtitle,
            'link'       => $request->link,
            'position'   => $position,
            'is_active'  => $request->has('is_active'),
            'sort_order' => $request->sort_order ?? 0,
            'starts_at'  => $request->starts_at ?: null,
            'ends_at'    => $request->ends_at ?: null,
        ];

        if ($request->hasFile('image')) {
            // Hapus gambar lama
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return redirect()->route('admin.banners')->with('success', 'Banner berhasil diperbarui!');
    }

    /**
     * Toggle status aktif/nonaktif banner.
     */
    public function toggle(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);

        $status = $banner->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.banners')->with('success', "Banner berhasil {$status}!");
    }

    /**
     * Hapus banner.
     */
    public function destroy(Banner $banner)
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return redirect()->route('admin.banners')->with('success', 'Banner berhasil dihapus!');
    }
}
