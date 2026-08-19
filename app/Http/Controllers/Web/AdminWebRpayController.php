<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RpayTransaction;
use App\Models\RpayWithdrawal;
use App\Models\User;
use App\Services\RpayService;
use App\Support\Spreadsheet\XlsxWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Pemantauan R_Pay untuk admin dan akun management.
 *
 * Menu ini hanya membaca — saldo tidak pernah diubah dari sini kecuali lewat
 * pemrosesan pencairan, yang tetap melalui RpayService supaya buku besarnya
 * selalu terisi.
 */
class AdminWebRpayController extends Controller
{
    public function __construct(private RpayService $rpay)
    {
    }

    /** Daftar akun yang punya saldo atau pernah bertransaksi R_Pay. */
    public function index(Request $request)
    {
        $cari  = trim((string) $request->query('cari', ''));
        $hanya = $request->query('hanya', 'bersaldo');

        $akun = User::query()
            ->withCount(['rpayTransactions as jumlah_mutasi' => fn ($q) => $q->reorder()])
            ->when($hanya === 'bersaldo', fn ($q) => $q->where('rpay_balance', '>', 0))
            ->when($hanya === 'pernah', fn ($q) => $q->has('rpayTransactions'))
            ->when($cari !== '', fn ($q) => $q->where(fn ($s) => $s
                ->where('name', 'like', "%{$cari}%")
                ->orWhere('email', 'like', "%{$cari}%")))
            ->orderByDesc('rpay_balance')
            ->paginate(20)
            ->withQueryString();

        return view('admin.rpay', [
            'akun'  => $akun,
            'cari'  => $cari,
            'hanya' => $hanya,
            'stats' => $this->ringkasan(),
        ]);
    }

    /** Rincian mutasi satu akun. */
    public function show(int $id)
    {
        $pemilik = User::findOrFail($id);

        $mutasi = RpayTransaction::where('user_id', $id)
            ->with('dibuatOleh')
            ->latest('id')
            ->paginate(25);

        // Saldo dihitung ulang dari buku besar, bukan dibaca dari kolom cache,
        // supaya selisih (bila ada) langsung kelihatan di halaman ini.
        $saldoBukuBesar = $this->rpay->saldo($id);

        return view('admin.rpay-show', compact('pemilik', 'mutasi', 'saldoBukuBesar'));
    }

    /** Antrean pencairan ke rekening bank. */
    public function withdrawals(Request $request)
    {
        $status = $request->query('status', 'pending');

        $daftar = RpayWithdrawal::with(['user', 'diprosesOleh'])
            ->when(in_array($status, ['pending', 'processing', 'completed', 'rejected'], true),
                fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'pending'    => RpayWithdrawal::where('status', 'pending')->count(),
            'processing' => RpayWithdrawal::where('status', 'processing')->count(),
            'nilai_antre' => RpayWithdrawal::whereIn('status', ['pending', 'processing'])->sum('amount'),
            'nilai_cair'  => RpayWithdrawal::where('status', 'completed')->sum('amount'),
        ];

        return view('admin.rpay-withdrawals', compact('daftar', 'stats', 'status'));
    }

    /**
     * Memproses satu pengajuan pencairan.
     *
     * Saldo pembeli sudah dipotong sejak pengajuan dibuat — supaya dana yang
     * sedang diproses tidak bisa dipakai belanja. Karena itu, menolak
     * pencairan harus mengembalikan saldonya.
     */
    public function processWithdrawal(Request $request, int $id)
    {
        $data = $request->validate([
            'keputusan'   => 'required|in:processing,completed,rejected',
            'admin_notes' => 'required_if:keputusan,rejected|nullable|string|max:1000',
        ], [
            'admin_notes.required_if' => 'Alasan penolakan wajib diisi agar pembeli tahu penyebabnya.',
        ]);

        $pencairan = RpayWithdrawal::findOrFail($id);

        if (in_array($pencairan->status, ['completed', 'rejected'], true)) {
            return back()->with('error', 'Pencairan ini sudah selesai diproses.');
        }

        DB::transaction(function () use ($pencairan, $data) {
            $pencairan->update([
                'status'       => $data['keputusan'],
                'admin_notes'  => $data['admin_notes'] ?? $pencairan->admin_notes,
                'processed_by' => Auth::id(),
                'processed_at' => in_array($data['keputusan'], ['completed', 'rejected'], true) ? now() : null,
            ]);

            if ($data['keputusan'] !== 'rejected') {
                return;
            }

            if ($this->rpay->sudahDibukukan($pencairan, 'reversal')) {
                return;
            }

            $this->rpay->kredit(
                $pencairan->user_id,
                (float) $pencairan->amount,
                'reversal',
                'Pencairan ' . $pencairan->reference . ' ditolak, saldo dikembalikan',
                $pencairan,
                Auth::id()
            );
        });

        return back()->with('success', match ($data['keputusan']) {
            'processing' => 'Pencairan ditandai sedang diproses.',
            'completed'  => 'Pencairan ditandai selesai.',
            'rejected'   => 'Pencairan ditolak dan saldo sudah dikembalikan ke R_Pay pembeli.',
        });
    }

    /** Laporan R_Pay dalam berkas Excel. */
    public function export(Request $request): BinaryFileResponse
    {
        $dari  = $request->query('dari');
        $ke    = $request->query('ke');

        $mutasi = RpayTransaction::with('user')
            ->when($dari, fn ($q) => $q->whereDate('created_at', '>=', $dari))
            ->when($ke, fn ($q) => $q->whereDate('created_at', '<=', $ke))
            ->orderBy('id')
            ->get();

        $penulis = new XlsxWriter;
        $penulis->setSheetName('Mutasi R_Pay')
            ->setWidths([12, 20, 28, 30, 12, 16, 18, 22, 40])
            ->setHeaderRows(1)
            ->addRow([
                'Tanggal', 'ID Akun', 'Nama Akun', 'Email',
                'Arah', 'Nominal', 'Saldo Sesudah', 'Sumber', 'Keterangan',
            ]);

        foreach ($mutasi as $baris) {
            $penulis->addRow([
                $baris->created_at->format('Y-m-d H:i'),
                $baris->user_id,
                $baris->user?->name ?? '(akun terhapus)',
                $baris->user?->email ?? '-',
                $baris->arah_label,
                (float) $baris->amount,
                (float) $baris->balance_after,
                $baris->sumber_label,
                $baris->description,
            ]);
        }

        // Baris ringkasan di bawah, supaya laporan bisa langsung dibaca
        // tanpa perlu membuat rumus sendiri.
        $masuk  = $mutasi->where('direction', 'credit')->sum('amount');
        $keluar = $mutasi->where('direction', 'debit')->sum('amount');

        $penulis->addRow([])
            ->addRow(['RINGKASAN'])
            ->addRow(['Total saldo masuk', '', '', '', '', (float) $masuk])
            ->addRow(['Total saldo keluar', '', '', '', '', (float) $keluar])
            ->addRow(['Selisih', '', '', '', '', (float) ($masuk - $keluar)])
            ->addRow(['Jumlah baris mutasi', '', '', '', '', $mutasi->count()])
            ->addRow(['Dibuat pada', '', '', '', '', now()->format('Y-m-d H:i')]);

        $namaBerkas = 'laporan-rpay-' . now()->format('Ymd-His') . '.xlsx';
        $lokasi     = storage_path('app/' . $namaBerkas);

        $penulis->save($lokasi);

        return response()->download($lokasi, $namaBerkas)->deleteFileAfterSend();
    }

    private function ringkasan(): array
    {
        return [
            'akun_bersaldo' => User::where('rpay_balance', '>', 0)->count(),
            'total_saldo'   => (float) User::sum('rpay_balance'),
            'masuk'         => (float) RpayTransaction::where('direction', 'credit')->sum('amount'),
            'keluar'        => (float) RpayTransaction::where('direction', 'debit')->sum('amount'),
        ];
    }
}
