<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminWebVoucherController extends Controller
{
    /**
     * Halaman utama kelola voucher.
     */
    public function index(Request $request)
    {
        $query = Voucher::with(['usedByUser', 'creator', 'reservedByUser', 'order']);

        // Eager load category jika kolom tersedia di database
        if (Schema::hasColumn('vouchers', 'category_id')) {
            $query->with('category');

            // Filter kategori
            if ($request->filled('category_id')) {
                if ($request->category_id === 'all') {
                    $query->whereNull('category_id');
                } else {
                    $query->where('category_id', $request->category_id);
                }
            }
        }

        // Filter status
        if ($request->filled('status')) {
            match ($request->status) {
                'available' => $query->where('is_used', false)->whereNull('used_by')->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                }),
                'claimed'   => $query->where('is_used', false)->whereNotNull('used_by'),
                'used'      => $query->used(),
                'expired'   => $query->expired(),
                default     => null,
            };
        }

        // Filter batch
        if ($request->filled('batch')) {
            $query->where('batch_label', $request->batch);
        }

        // Pencarian kode
        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . strtoupper($request->search) . '%');
        }

        $vouchers = $query->latest()->paginate(50)->withQueryString();

        // Statistik
        $stats = [
            'total'     => Voucher::count(),
            'available' => Voucher::where('is_used', false)->whereNull('used_by')->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })->count(),
            'claimed'   => Voucher::where('is_used', false)->whereNotNull('used_by')->count(),
            'used'      => Voucher::used()->count(),
            'expired'   => Voucher::expired()->count(),
        ];

        // Daftar batch unik
        $batches = Voucher::whereNotNull('batch_label')
            ->distinct()
            ->pluck('batch_label')
            ->sort()
            ->values();

        // Ambil kategori secara aman
        $categoryQuery = Category::query();
        if (Schema::hasColumn('categories', 'is_active')) {
            $categoryQuery->where('is_active', true);
        }
        if (Schema::hasColumn('categories', 'sort_order')) {
            $categoryQuery->orderBy('sort_order');
        } else {
            $categoryQuery->orderBy('name');
        }
        $categories = $categoryQuery->get();

        return view('admin.vouchers', compact('vouchers', 'stats', 'batches', 'categories'));
    }

    /**
     * Generate voucher secara massal.
     */
    public function generate(Request $request)
    {
        // Sanitasi amount jika ada format titik/ribuan
        if ($request->has('amount')) {
            $cleaned = preg_replace('/\D/', '', (string) $request->input('amount'));
            $request->merge(['amount' => $cleaned !== '' ? (int) $cleaned : null]);
        }
        $validated = $request->validate([
            'quantity'    => 'required|integer|min:1|max:500',
            'amount'      => 'required|integer|min:1000',
            'expires_at'  => 'nullable|date|after:today',
            'batch_label' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
        ], [
            'quantity.required' => 'Jumlah voucher wajib diisi.',
            'quantity.max'      => 'Maksimal 500 voucher per batch.',
            'amount.required'   => 'Nominal voucher wajib diisi.',
            'amount.min'        => 'Nominal minimal Rp 1.000.',
            'expires_at.after'  => 'Tanggal kadaluarsa harus setelah hari ini.',
        ]);

        $expiresAt = $validated['expires_at']
            ? \Carbon\Carbon::parse($validated['expires_at'])->endOfDay()
            : null;

        $batchLabel = $validated['batch_label']
            ?: 'Batch ' . now()->format('d M Y H:i');

        $hasCategoryCol = Schema::hasColumn('vouchers', 'category_id');

        $codes = [];
        DB::transaction(function () use ($validated, $expiresAt, $batchLabel, $hasCategoryCol, $request, &$codes) {
            for ($i = 0; $i < $validated['quantity']; $i++) {
                $voucherData = [
                    'code'        => Voucher::generateUniqueCode(),
                    'amount'      => $validated['amount'],
                    'expires_at'  => $expiresAt,
                    'batch_label' => $batchLabel,
                    'created_by'  => Auth::id(),
                ];

                if ($hasCategoryCol && $request->filled('category_id')) {
                    $voucherData['category_id'] = $request->category_id;
                }

                $voucher = Voucher::create($voucherData);
                $codes[] = $voucher->code;
            }
        });

        return redirect()
            ->route('admin.vouchers', ['batch' => $batchLabel])
            ->with('success', count($codes) . ' voucher berhasil di-generate dengan batch "' . $batchLabel . '".');
    }

    /**
     * Halaman cetak voucher (format F4).
     */
    public function print(Request $request)
    {
        $query = Voucher::query();

        if ($request->filled('batch')) {
            $query->where('batch_label', $request->batch);
        }

        if ($request->filled('ids')) {
            $ids = explode(',', $request->ids);
            $query->whereIn('id', $ids);
        }

        // Hanya cetak voucher yang belum dipakai
        $vouchers = $query->where('is_used', false)->orderBy('id')->get();

        if ($vouchers->isEmpty()) {
            return back()->with('error', 'Tidak ada voucher yang bisa dicetak.');
        }

        return view('admin.vouchers-print', compact('vouchers'));
    }

    /**
     * Hapus voucher yang belum digunakan (satu atau batch).
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'voucher_ids'   => 'nullable|array',
            'voucher_ids.*' => 'integer|exists:vouchers,id',
            'batch_label'   => 'nullable|string',
        ]);

        if (!empty($validated['voucher_ids'])) {
            $count = Voucher::whereIn('id', $validated['voucher_ids'])->delete();

            return back()->with('success', $count . ' voucher berhasil dihapus.');
        }

        if (!empty($validated['batch_label'])) {
            $count = Voucher::where('batch_label', $validated['batch_label'])->delete();

            return redirect()
                ->route('admin.vouchers')
                ->with('success', $count . ' voucher pada batch "' . $validated['batch_label'] . '" berhasil dihapus.');
        }

        return back()->with('error', 'Pilih voucher atau batch yang ingin dihapus.');
    }
}