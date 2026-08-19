<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderExport;
use App\Services\TransactionFeeService;
use App\Support\Spreadsheet\XlsxWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Ekspor pesanan ke XLSX.
 */
class AdminWebOrderExportController extends Controller
{
    private const FOLDER = 'ekspor-pesanan';

    public function store(Request $request)
    {
        $data = $request->validate([
            'dari'   => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ], [
            'sampai.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.',
        ]);

        $dari   = $data['dari']   ?? null;
        $sampai = $data['sampai'] ?? null;

        $pesanan = Order::with(['user', 'items.product', 'items.productVariant', 'returns'])
            ->when($dari,   fn ($q) => $q->whereDate('created_at', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('created_at', '<=', $sampai))
            ->orderBy('id')
            ->get();

        if ($pesanan->isEmpty()) {
            return back()->with('error', 'Tidak ada pesanan pada rentang tanggal itu, '
                . 'jadi tidak ada yang bisa diekspor.');
        }

        $jasaBiaya = app(TransactionFeeService::class);

        $penulis = new XlsxWriter;
        $penulis->setSheetName('Pesanan')
            ->setWidths([
                20, 16, 20, 22, 18, 14,      // pesanan & pengiriman
                18, 18, 18,                   // waktu
                16, 14,                       // pembayaran
                26, 40, 24, 22,               // produk
                14, 10, 16,                   // harga & jumlah
                12, 14,                       // berat
                14, 14, 14, 16,               // uang pesanan
                14, 16, 14, 16,               // biaya & bersih
                20, 24, 16, 40, 20, 18, 12,   // pembeli & alamat
                30,                           // catatan
            ])
            ->setHeaderRows(1)
            ->addRow([
                'No. Pesanan', 'Status Pesanan', 'Status Pengembalian', 'No. Resi',
                'Opsi Pengiriman', 'Tipe Pesanan',
                'Waktu Pesanan Dibuat', 'Waktu Dikirim', 'Waktu Pesanan Selesai',
                'Metode Pembayaran', 'Status Bayar',
                'SKU Variasi', 'Nama Produk', 'Nama Variasi', 'Kategori',
                'Harga Satuan', 'Jumlah', 'Subtotal Produk',
                'Berat Satuan (g)', 'Total Berat (g)',
                'Subtotal Pesanan', 'Ongkir Dibayar Pembeli', 'Diskon Referal', 'Total Pembayaran',
                'Biaya Midtrans', 'Ongkir ke Kurir', 'Komisi Referal', 'Penghasilan Bersih',
                'Akun Pembeli', 'Email Pembeli', 'Nama Penerima', 'Alamat Pengiriman',
                'Kota/Kabupaten', 'Provinsi', 'Kode Pos',
                'Catatan Pembeli',
            ]);

        $beratSatuan = (int) config('pengiriman.berat_per_pasang_gram', 800);
        $jumlahBaris = 0;

        foreach ($pesanan as $o) {
            $alamat = (array) $o->shipping_address;
            $retur  = $o->returns->firstWhere('type', 'return');

            [$kodeKurir, $jenisLayanan] = array_pad(explode(':', (string) $o->courier_code, 2), 2, null);

            $instan = in_array($kodeKurir, ['gojek', 'grab', 'lalamove', 'borzo'], true)
                || str_contains(strtolower((string) $jenisLayanan), 'instant');

            $dibayar = (int) round((float) $o->grand_total);
            $komisi  = (int) round((float) ($o->referral_commission ?? 0));

            // Angka biaya dipakai apa adanya bila sudah tercatat.
            $biayaTercatat = (float) $o->midtrans_fee;
            $biayaBayar = (int) round($biayaTercatat > 0
                ? $biayaTercatat
                : $jasaBiaya->hitungBiayaMidtrans((string) $o->payment_method, $dibayar));

            $ongkirTercatat = (float) $o->shipping_actual_cost;
            $ongkirAsli = (int) round($ongkirTercatat > 0
                ? $ongkirTercatat
                : (float) $o->shipping_cost);

            $bersihTercatat = $o->net_revenue;
            $bersih = $bersihTercatat !== null && $biayaTercatat > 0
                ? (int) round((float) $bersihTercatat)
                : $dibayar - $biayaBayar - $ongkirAsli - $komisi;

            // Pesanan tanpa rincian barang tetap muncul satu baris, supaya
            // tidak hilang diam-diam dari laporan.
            $barang = $o->items->isNotEmpty() ? $o->items : collect([null]);

            foreach ($barang as $item) {
                $jumlah   = $item ? (int) $item->quantity : 0;
                $harga    = $item ? (float) $item->price : 0;

                $penulis->addRow([
                    $o->order_number,
                    $o->status_label ?? $o->status,
                    $retur ? ($retur->return_number . ' — ' . $retur->status_label) : '',
                    $o->tracking_number ?? '',
                    $o->courier ?? '',
                    $instan ? 'Instant' : 'Reguler',

                    $o->created_at?->format('Y-m-d H:i') ?? '',
                    $o->status === 'shipped' || $o->tracking_number
                        ? ($o->updated_at?->format('Y-m-d H:i') ?? '') : '',
                    $o->completed_at?->format('Y-m-d H:i') ?? '',

                    $o->payment_method ?? '',
                    $o->payment_status === 'paid' ? 'Lunas' : 'Belum Lunas',

                    $item?->productVariant?->sku ?? '',
                    $item?->product_name ?? '(tanpa rincian barang)',
                    $item?->variant_info ?? '',
                    $item?->product?->category?->name ?? '',

                    $harga,
                    $jumlah,
                    $harga * $jumlah,

                    $item ? $beratSatuan : 0,
                    $item ? $beratSatuan * $jumlah : 0,

                    (float) $o->total_price,
                    (float) $o->shipping_cost,
                    (float) ($o->referral_discount ?? 0),
                    $dibayar,

                    $biayaBayar,
                    $ongkirAsli,
                    $komisi,
                    $bersih,

                    $o->user?->name ?? 'Guest',
                    $o->user?->email ?? '',
                    $alamat['recipient_name'] ?? '',
                    $alamat['address_line'] ?? '',
                    $alamat['city'] ?? '',
                    $alamat['province'] ?? '',
                    (string) ($alamat['postal_code'] ?? ''),

                    $o->notes ?? '',
                ]);

                $jumlahBaris++;
            }
        }

        // Nama berkas memuat rentangnya, supaya isi folder unduhan tetap bisa dibedakan tanpa perlu membuka...
        $nama = 'Pesanan_'
            . ($dari   ? str_replace('-', '', $dari)   : 'awal') . '_'
            . ($sampai ? str_replace('-', '', $sampai) : 'akhir') . '_'
            . now()->format('Ymd_His') . '_'
            . strtolower(\Illuminate\Support\Str::random(4)) . '.xlsx';

        $jalur = self::FOLDER . '/' . $nama;
        $penuh = Storage::disk('local')->path($jalur);

        Storage::disk('local')->makeDirectory(self::FOLDER);
        $penulis->save($penuh);

        OrderExport::create([
            'user_id'        => Auth::id(),
            'file_name'      => $nama,
            'path'           => $jalur,
            'dari'           => $dari,
            'sampai'         => $sampai,
            'jumlah_pesanan' => $pesanan->count(),
            'jumlah_baris'   => $jumlahBaris,
            'ukuran'         => filesize($penuh) ?: 0,
        ]);

        $dibuang = OrderExport::pangkasRiwayat();

        return back()->with('success',
            'Ekspor selesai: ' . $pesanan->count() . ' pesanan, ' . $jumlahBaris . ' baris. '
            . 'Berkasnya ada di Riwayat Download.'
            . ($dibuang > 0
                ? ' ' . $dibuang . ' berkas terlama dihapus karena riwayat dibatasi '
                    . OrderExport::BATAS_RIWAYAT . ' berkas.'
                : ''));
    }

    public function download(int $id)
    {
        $ekspor = OrderExport::findOrFail($id);

        if (! $ekspor->berkas_ada) {
            return back()->with('error', 'Berkasnya sudah tidak ada di peladen. '
                . 'Silakan buat ekspor baru.');
        }

        return Storage::disk('local')->download($ekspor->path, $ekspor->file_name);
    }

    public function destroy(int $id)
    {
        $ekspor = OrderExport::findOrFail($id);

        if (filled($ekspor->path)) {
            Storage::disk('local')->delete($ekspor->path);
        }

        $ekspor->delete();

        return back()->with('success', 'Berkas ekspor dihapus.');
    }
}
