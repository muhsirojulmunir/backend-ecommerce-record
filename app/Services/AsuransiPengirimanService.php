<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * Memutuskan apakah sebuah pengiriman perlu diasuransikan.
 *
 * Dasarnya sederhana: asuransi hanya berguna kalau ganti rugi TANPA asuransi
 * tidak cukup menutup nilai barang.
 *
 * Tanpa asuransi, penggantian dibatasi pada yang terkecil antara sekian kali
 * ongkos kirim atau nilai barangnya, dengan plafon tertentu. Untuk barang murah
 * berongkir mahal, batas itu sudah menutup semuanya — membeli asuransi berarti
 * membayar untuk perlindungan yang sudah dimiliki. Untuk barang mahal berongkir
 * murah, selisihnya besar dan justru di situlah asuransi bekerja.
 *
 * Contoh nyata dari toko ini: barang Rp 255.000 dengan ongkir Rp 16.000.
 * Tanpa asuransi, penggantiannya 10 x 16.000 = Rp 160.000 — kurang Rp 95.000
 * dari nilai barangnya. Preminya sekitar Rp 1.275. Jadi diasuransikan.
 */
class AsuransiPengirimanService
{
    /**
     * Nilai barang yang layak diasuransikan pada satu pesanan.
     *
     * Yang dihitung hanya harga barangnya, bukan ongkir: kalau paket hilang,
     * yang perlu diganti adalah barangnya. Ongkirnya sudah terlanjur menjadi
     * jasa yang tidak tersampaikan, dan itu urusan terpisah.
     */
    public function nilaiBarang(Order $order): int
    {
        $dariItem = (int) round((float) $order->items->sum(
            fn ($item) => (float) $item->price * (int) $item->quantity
        ));

        if ($dariItem > 0) {
            return $dariItem;
        }

        // Pesanan tanpa rincian barang: pakai total dikurangi ongkir.
        return max(0, (int) round((float) $order->grand_total - (float) $order->shipping_cost));
    }

    /**
     * Ganti rugi yang didapat bila pengiriman TIDAK diasuransikan.
     */
    public function gantiRugiTanpaAsuransi(int $nilaiBarang, int $ongkir): int
    {
        $kelipatan = (int) config('asuransi.kelipatan_ongkir', 10);
        $plafon    = (int) config('asuransi.plafon_tanpa_asuransi', 1_000_000);

        return min($ongkir * $kelipatan, $nilaiBarang, $plafon);
    }

    /**
     * Berapa nilai yang harus diasuransikan untuk pesanan ini?
     * Nol berarti tidak perlu.
     *
     * @return array{nilai: int, alasan: string}
     */
    public function putuskan(Order $order): array
    {
        $mode = (string) config('asuransi.mode', 'otomatis');

        if ($mode === 'mati') {
            return ['nilai' => 0, 'alasan' => 'Asuransi dimatikan lewat pengaturan.'];
        }

        $nilaiBarang = $this->nilaiBarang($order);

        if ($nilaiBarang <= 0) {
            return ['nilai' => 0, 'alasan' => 'Nilai barang tidak diketahui.'];
        }

        $minimum = (int) config('asuransi.nilai_minimum', 50_000);

        if ($nilaiBarang < $minimum) {
            return [
                'nilai'  => 0,
                'alasan' => 'Nilai barang di bawah batas terkecil yang diasuransikan.',
            ];
        }

        if ($mode === 'selalu') {
            return ['nilai' => $nilaiBarang, 'alasan' => 'Semua pengiriman diasuransikan.'];
        }

        // Mode otomatis: bandingkan dengan ganti rugi tanpa asuransi.
        $ongkirAsli = (int) round(
            (float) $order->shipping_actual_cost > 0
                ? (float) $order->shipping_actual_cost
                : (float) $order->shipping_cost
        );

        $tanpaAsuransi = $this->gantiRugiTanpaAsuransi($nilaiBarang, $ongkirAsli);

        if ($tanpaAsuransi >= $nilaiBarang) {
            return [
                'nilai'  => 0,
                'alasan' => 'Ganti rugi tanpa asuransi sudah menutup nilai barangnya.',
            ];
        }

        return [
            'nilai'  => $nilaiBarang,
            'alasan' => 'Tanpa asuransi hanya diganti Rp ' . number_format($tanpaAsuransi, 0, ',', '.')
                . ' dari Rp ' . number_format($nilaiBarang, 0, ',', '.') . '.',
        ];
    }

    /**
     * Apakah premi yang ditagihkan Biteship masih masuk akal?
     *
     * Preminya baru diketahui setelah pesanan terbentuk, jadi ini pemeriksaan
     * setelah kejadian — gunanya menyalakan tanda bahaya di berkas catatan,
     * bukan membatalkan pengiriman yang sudah berjalan.
     */
    public function periksaPremi(Order $order, int $premi, int $nilaiDiasuransikan): void
    {
        if ($premi <= 0 || $nilaiDiasuransikan <= 0) {
            return;
        }

        $batas = (float) config('asuransi.batas_premi_persen', 2.0);
        $persen = $premi / $nilaiDiasuransikan * 100;

        if ($persen > $batas) {
            Log::warning('Premi asuransi Biteship di luar kewajaran', [
                'pesanan'       => $order->order_number,
                'premi'         => $premi,
                'nilai'         => $nilaiDiasuransikan,
                'persen'        => round($persen, 3),
                'batas_persen'  => $batas,
            ]);
        }
    }
}
