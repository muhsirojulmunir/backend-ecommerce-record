<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use App\Services\RpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Peninjauan pengajuan pengembalian barang oleh admin.
 */
class AdminWebReturnController extends Controller
{
    public function __construct(
        private RpayService $rpay,
        private \App\Services\BiteshipReturnService $biteshipReturn
    ) {
    }

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $cari   = trim((string) $request->query('cari', ''));

        $daftar = OrderReturn::with(['order', 'user'])
            ->where('type', 'return')
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true),
                fn ($q) => $q->where('status', $status))
            ->when($cari !== '', function ($q) use ($cari) {
                $q->where(function ($sub) use ($cari) {
                    $sub->whereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$cari}%"))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$cari}%")
                            ->orWhere('email', 'like', "%{$cari}%"));
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'pending'  => OrderReturn::where('type', 'return')->where('status', 'pending')->count(),
            'approved' => OrderReturn::where('type', 'return')->where('status', 'approved')->count(),
            'rejected' => OrderReturn::where('type', 'return')->where('status', 'rejected')->count(),
            'nilai'    => OrderReturn::where('type', 'return')->where('status', 'approved')->sum('refund_amount'),
        ];

        return view('admin.returns', compact('daftar', 'stats', 'status', 'cari'));
    }

    public function show(int $id)
    {
        $pengajuan = OrderReturn::with(['order.items', 'user', 'diputuskanOleh'])->findOrFail($id);

        return view('admin.returns-show', compact('pengajuan'));
    }

    /**
 * Tahap 1 — meninjau pengajuan.
 */
    public function decide(Request $request, int $id)
    {
        $data = $request->validate([
            'keputusan'   => 'required|in:approved,rejected',
            'admin_notes' => 'required_if:keputusan,rejected|nullable|string|max:1000',
        ], [
            'admin_notes.required_if' => 'Alasan penolakan wajib diisi agar pembeli tahu penyebabnya.',
        ]);

        $pengajuan = OrderReturn::with(['order.items', 'user'])->findOrFail($id);

        if ($pengajuan->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah pernah ditinjau.');
        }

        $disetujui = $data['keputusan'] === 'approved';
        $returBiteship = null;

        if ($disetujui) {
            // Panggil API Biteship untuk terbitkan resi retur & booking penjemputan otomatis
            $returBiteship = $this->biteshipReturn->createReturnShipment($pengajuan);
        }

        $updateData = [
            'status'      => $disetujui ? ($returBiteship ? 'shipped_back' : 'approved') : 'rejected',
            'admin_notes' => $data['admin_notes'] ?? null,
            'decided_by'  => Auth::id(),
            'approved_at' => $disetujui ? now() : null,
            'resolved_at' => $disetujui ? null : now(),
        ];

        if ($returBiteship) {
            $updateData['return_courier']         = $returBiteship['courier'];
            $updateData['return_tracking_number'] = $returBiteship['tracking_number'];
            $updateData['shipped_back_at']        = now();
        }

        /*
         * Kalau Biteship gagal, pengajuannya berhenti di status "approved" —
         * disetujui, tetapi penjemputannya belum dipesan.
         *
         * Dulu di sini dikarang resi "RTR-BITESHIP-00000001", statusnya
         * dinaikkan ke "shipped_back", dan admin diberi tahu bahwa resinya
         * "berhasil terbit via Biteship". Tiga-tiganya tidak benar: tidak ada
         * kurir yang dipesan, dan pembeli menunggu penjemputan yang tidak akan
         * pernah datang sambil memegang nomor resi yang tidak berarti apa-apa.
         */
        $pengajuan->update($updateData);

        if (! $disetujui) {
            return redirect()
                ->route('admin.returns.show', $pengajuan->id)
                ->with('success', 'Pengajuan ditolak dan alasannya sudah tercatat untuk pembeli.');
        }

        if (! $returBiteship) {
            $alasan = $this->biteshipReturn->alasanGagalTerakhir()
                ?: 'Biteship tidak mengembalikan jawaban yang bisa dipakai.';

            return redirect()
                ->route('admin.returns.show', $pengajuan->id)
                ->with('error',
                    'Retur DISETUJUI, tetapi penjemputan kurir BELUM terpesan. ' . $alasan
                    . ' Perbaiki penyebabnya lalu pesan ulang penjemputan, atau isi nomor resi '
                    . 'secara manual bila kamu mengaturnya sendiri. Pembeli belum bisa melihat resi apa pun.');
        }

        return redirect()
            ->route('admin.returns.show', $pengajuan->id)
            ->with('success',
                'Pengajuan retur disetujui dan penjemputan sudah dipesan ke Biteship ('
                . $pengajuan->return_courier . ' — ' . $pengajuan->return_tracking_number . '). '
                . 'Pembeli sudah bisa melihat resi penjemputannya di detail pesanan.');
    }

    /**
     * Tahap 2 — menandai barang sudah sampai di toko.
     */
    public function terima(int $id)
    {
        $pengajuan = OrderReturn::findOrFail($id);

        if ($pengajuan->status !== 'shipped_back') {
            return back()->with('error', $pengajuan->status === 'approved'
                ? 'Pembeli belum mencatatkan nomor resi pengiriman baliknya.'
                : 'Barang untuk pengajuan ini sudah pernah ditandai diterima.');
        }

        $pengajuan->update([
            'status'      => 'received',
            'received_at' => now(),
        ]);

        return back()->with('success', 'Barang ditandai sudah diterima. '
            . 'Silakan periksa kondisinya, lalu putuskan hasil akhirnya.');
    }

    /**
 * Tahap 3 — hasil pemeriksaan barang, sekaligus penentuan akhir.
 */
    public function finalize(Request $request, int $id)
    {
        $pengajuan = OrderReturn::with('order')->findOrFail($id);

        // Batas atas dijaga di server juga, bukan hanya di kolom isian.
        // Pengembalian tidak boleh melebihi yang benar-benar dibayar pembeli —
        // salah ketik satu angka nol saja bisa mengeluarkan sepuluh kali lipat.
        $batas = (float) ($pengajuan->order?->grand_total ?? 0);

        $data = $request->validate([
            'hasil'            => 'required|in:completed,rejected',
            'inspection_notes' => 'required|string|max:1000',
            'nominal'          => 'nullable|numeric|min:0|max:' . $batas,
        ], [
            'inspection_notes.required' => 'Tuliskan hasil pemeriksaan barangnya agar pembeli tahu dasar keputusannya.',
            'nominal.max' => 'Nominal melebihi total yang dibayar pembeli (Rp '
                . number_format($batas, 0, ',', '.') . ').',
        ]);

        if ($pengajuan->status !== 'received') {
            return back()->with('error', 'Barang untuk pengajuan ini belum ditandai diterima.');
        }

        DB::transaction(function () use ($pengajuan, $data) {
            /*
             * Diperiksa ulang sambil dikunci. Dua admin yang memutuskan
             * bersamaan — atau satu tombol yang terklik dua kali — sama-sama
             * lolos pemeriksaan status di atas dan sama-sama mengeluarkan
             * pengembalian dana. Yang kedua berhenti di sini.
             */
            $terkunci = OrderReturn::whereKey($pengajuan->getKey())->lockForUpdate()->first();

            if (! $terkunci || $terkunci->status !== 'received') {
                return;
            }

            $lolos = $data['hasil'] === 'completed';

            // Nominal bawaan adalah SELURUH yang dibayar pembeli, termasuk ongkos kirim.
            $nominal = $lolos && $pengajuan->resolution === 'refund'
                ? (float) ($data['nominal'] ?? $pengajuan->order->grand_total)
                : null;

            $pengajuan->update([
                'status'           => $data['hasil'],
                'inspection_notes' => $data['inspection_notes'],
                'refund_amount'    => $nominal,
                'decided_by'       => Auth::id(),
                'resolved_at'      => now(),
            ]);

            if (! $lolos || $pengajuan->resolution !== 'refund' || ! $nominal) {
                return;
            }

            // Penjagaan terakhir terhadap penekanan tombol berganda.
            if ($this->rpay->sudahDibukukan($pengajuan, 'refund')) {
                return;
            }

            $this->rpay->kredit(
                $pengajuan->user_id,
                $nominal,
                'refund',
                'Pengembalian dana pesanan ' . $pengajuan->order->order_number,
                $pengajuan,
                Auth::id()
            );
        });

        return back()->with('success', $data['hasil'] === 'completed'
            ? ($pengajuan->resolution === 'refund'
                ? 'Pemeriksaan lolos. Dana sudah masuk ke R_Pay pembeli.'
                : 'Pemeriksaan lolos. Silakan kirimkan barang penggantinya.')
            : 'Pengajuan ditolak setelah pemeriksaan. Alasannya sudah tercatat untuk pembeli.');
    }
}
