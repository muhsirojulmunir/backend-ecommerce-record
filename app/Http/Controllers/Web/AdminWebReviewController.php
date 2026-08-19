<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Peninjauan ulasan pembeli oleh admin.
 *
 * Ulasan tayang seketika begitu pembeli mengirimnya — halaman produk yang
 * lama kosong tidak menolong siapa pun. Peran admin di sini bukan penjaga
 * gerbang melainkan penyapu: menyembunyikan ulasan yang kasar, spam, atau
 * salah alamat.
 *
 * Menyembunyikan didahulukan daripada menghapus. Ulasan yang disembunyikan
 * masih bisa ditampilkan kembali bila keputusannya ternyata keliru, dan
 * jejak siapa yang menyembunyikan beserta alasannya tetap tersimpan.
 */
class AdminWebReviewController extends Controller
{
    public function index(Request $request)
    {
        $status  = $request->query('status', 'semua');
        $bintang = (int) $request->query('bintang', 0);
        $cari    = trim((string) $request->query('cari', ''));

        $daftar = ProductReview::with(['product:id,name,slug,image', 'user:id,name,email', 'order:id,order_number'])
            ->when($status === 'tampil',      fn ($q) => $q->where('is_hidden', false))
            ->when($status === 'disembunyikan', fn ($q) => $q->where('is_hidden', true))
            ->when($bintang >= 1 && $bintang <= 5, fn ($q) => $q->where('rating', $bintang))
            ->when($cari !== '', function ($q) use ($cari) {
                $q->where(function ($sub) use ($cari) {
                    $sub->where('comment', 'like', "%{$cari}%")
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$cari}%"))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$cari}%")
                            ->orWhere('email', 'like', "%{$cari}%"))
                        ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$cari}%"));
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'semua'         => ProductReview::count(),
            'tampil'        => ProductReview::where('is_hidden', false)->count(),
            'disembunyikan' => ProductReview::where('is_hidden', true)->count(),
            // Bintang rendah didahulukan perhatiannya: di situlah keluhan
            // yang perlu ditindaklanjuti biasanya berada.
            'rendah'        => ProductReview::where('is_hidden', false)->where('rating', '<=', 2)->count(),
        ];

        $rataRata = round((float) ProductReview::where('is_hidden', false)->avg('rating'), 2);

        return view('admin.reviews', compact('daftar', 'stats', 'rataRata', 'status', 'bintang', 'cari'));
    }

    /**
     * Menyembunyikan ulasan, atau menampilkannya kembali.
     *
     * Satu jalur untuk dua arah, sebab keduanya adalah keputusan yang sama:
     * mengubah apakah ulasan ini boleh dilihat pengunjung.
     */
    public function toggle(Request $request, int $id)
    {
        $ulasan = ProductReview::findOrFail($id);

        $data = $request->validate([
            // Alasan hanya wajib saat menyembunyikan. Menampilkan kembali
            // tidak perlu dibela — mengembalikan ke keadaan semula.
            'alasan' => [$ulasan->is_hidden ? 'nullable' : 'required', 'string', 'max:255'],
        ], [
            'alasan.required' => 'Tuliskan dulu alasan ulasan ini disembunyikan.',
            'alasan.max'      => 'Alasannya maksimal 255 karakter.',
        ]);

        $sembunyikan = ! $ulasan->is_hidden;

        $ulasan->update([
            'is_hidden'     => $sembunyikan,
            'hidden_reason' => $sembunyikan ? trim($data['alasan']) : null,
            'hidden_by'     => $sembunyikan ? Auth::id() : null,
            'hidden_at'     => $sembunyikan ? now() : null,
        ]);

        activity('ulasan')
            ->performedOn($ulasan)
            ->causedBy(Auth::user())
            ->withProperties([
                'produk' => $ulasan->product?->name,
                'alasan' => $sembunyikan ? trim($data['alasan']) : null,
            ])
            ->log($sembunyikan ? 'menyembunyikan ulasan' : 'menampilkan kembali ulasan');

        return back()->with('success', $sembunyikan
            ? 'Ulasan disembunyikan dan tidak lagi tampil di halaman produk.'
            : 'Ulasan ditampilkan kembali di halaman produk.');
    }

    /**
     * Menghapus ulasan beserta fotonya.
     *
     * Disediakan untuk yang benar-benar tidak layak disimpan — sebutan kasar
     * atau data pribadi orang lain. Untuk selebihnya, sembunyikan saja: yang
     * terhapus tidak bisa ditinjau ulang kalau ternyata keputusannya keliru.
     */
    public function destroy(int $id)
    {
        $ulasan = ProductReview::with('product')->findOrFail($id);
        $nama   = $ulasan->product?->name;

        // Berkas fotonya ikut dibuang, jangan sampai menumpuk tanpa pemilik.
        foreach ($ulasan->daftar_foto as $foto) {
            Storage::disk('public')->delete($foto);
        }

        activity('ulasan')
            ->performedOn($ulasan)
            ->causedBy(Auth::user())
            ->withProperties(['produk' => $nama, 'bintang' => $ulasan->rating])
            ->log('menghapus ulasan');

        $ulasan->delete();

        return redirect()
            ->route('admin.reviews')
            ->with('success', 'Ulasan dihapus permanen beserta fotonya.');
    }
}
