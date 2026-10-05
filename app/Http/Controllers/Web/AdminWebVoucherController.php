<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminWebVoucherController extends Controller
{
    /**
     * Halaman utama kelola voucher.
     */
    public function index(Request $request)
    {
        $query = Voucher::with(['usedByUser', 'creator']);

        // Filter status
        if ($request->filled('status')) {
            match ($request->status) {
                'available' => $query->available(),
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
            'available' => Voucher::available()->count(),
            'used'      => Voucher::used()->count(),
            'expired'   => Voucher::expired()->count(),
        ];

        // Daftar batch unik
        $batches = Voucher::whereNotNull('batch_label')
            ->distinct()
            ->pluck('batch_label')
            ->sort()
            ->values();

        return view('admin.vouchers', compact('vouchers', 'stats', 'batches'));
    }

    /**
     * Generate voucher secara massal.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'quantity'    => 'required|integer|min:1|max:500',
            'amount'     => 'required|integer|min:1000',
            'expires_at' => 'nullable|date|after:today',
            'batch_label' => 'nullable|string|max:100',
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

        $codes = [];
        DB::transaction(function () use ($validated, $expiresAt, $batchLabel, &$codes) {
            for ($i = 0; $i < $validated['quantity']; $i++) {
                $voucher = Voucher::create([
                    'code'        => Voucher::generateUniqueCode(),
                    'amount'      => $validated['amount'],
                    'expires_at'  => $expiresAt,
                    'batch_label' => $batchLabel,
                    'created_by'  => Auth::id(),
                ]);
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
            'voucher_ids'  => 'nullable|array',
            'voucher_ids.*' => 'integer|exists:vouchers,id',
            'batch_label'  => 'nullable|string',
        ]);

        $query = Voucher::where('is_used', false);

        if (!empty($validated['voucher_ids'])) {
            $query->whereIn('id', $validated['voucher_ids']);
        } elseif (!empty($validated['batch_label'])) {
            $query->where('batch_label', $validated['batch_label']);
        } else {
            return back()->with('error', 'Tidak ada voucher yang dipilih.');
        }

        $count = $query->count();
        $query->delete();

        return back()->with('success', $count . ' voucher berhasil dihapus.');
    }
}
