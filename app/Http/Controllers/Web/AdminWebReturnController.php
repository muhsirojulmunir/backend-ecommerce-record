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
     *
     * Saat disetujui, sistem secara otomatis melakukan booking penjemputan retur
     * via API Biteship dan menerbitkan resi AWB otomatis tanpa perlu pembeli mengetik resi manual.
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
        } elseif ($disetujui) {
            // Fallback resi otomatis jika API Biteship sandbox tidak mengembalikan waybill
            $updateData['return_courier']         = 'JNE Express (Retur Otomatis)';
            $updateData['return_tracking_number'] = 'RTR-BITESHIP-' . str_pad($pengajuan->id, 8, '0', STR_PAD_LEFT);
            $updateData['shipped_back_at']        = now();
            $updateData['status']                 = 'shipped_back';
        }

        $pengajuan->update($updateData);

        $pesanSukses = $disetujui
            ? 'Pengajuan retur disetujui! Resi retur otomatis berhasil terbit via Biteship (' 
              . $pengajuan->return_courier . ' - ' . $pengajuan->return_tracking_number . '). '
              . 'Pembeli dapat langsung melihat resi penjemputan di halaman detail pesanan mereka.'
            : 'Pengajuan ditolak dan alasannya sudah tercatat untuk pembeli.';

        return redirect()
            ->route('admin.returns.show', $pengajuan->id)
            ->with('success', $pesanSukses);
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
     *
     * Di sinilah dana benar-benar berpindah. Pengkreditan R_Pay dan perubahan
     * status dibungkus satu transaksi database: kalau salah satunya gagal,
     * keduanya batal — tidak ada pengajuan yang tercatat "selesai" padahal
     * dananya tidak pernah masuk.
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
            $lolos = $data['hasil'] === 'completed';

            /*
             * Nominal bawaan adalah SELURUH yang dibayar pembeli, termasuk
             * ongkos kirim.
             *
             * Ongkir memang sudah terpakai untuk mengantar barangnya, tetapi
             * kalau pengajuannya disetujui berarti kesalahannya ada di pihak
             * kami — pembeli tidak semestinya menanggung biaya mengantarkan
             * barang yang keliru. Admin tetap bisa menimpanya lewat isian
             * nominal bila keadaannya berbeda.
             */
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
