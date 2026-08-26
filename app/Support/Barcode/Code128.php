<?php

namespace App\Support\Barcode;

use InvalidArgumentException;

/**
 * Pembuat barcode Code 128 — yang benar-benar bisa dipindai.
 *
 * Label sebelumnya memakai garis hias dari CSS (`repeating-linear-gradient`).
 * Polanya sama persis di setiap label dan tidak menyimpan informasi apa pun,
 * jadi pemindai kurir tidak pernah bisa membacanya. Kelas ini menggantinya
 * dengan barcode sesungguhnya yang memuat nomor resi asli dari kurir.
 *
 * Code 128 dipilih karena itulah yang dipakai ekspedisi di Indonesia untuk
 * nomor resi: bisa memuat huruf maupun angka, padat, dan punya angka periksa
 * sehingga pemindai menolak hasil baca yang meleset.
 *
 * Ditulis sendiri, bukan memakai pustaka luar, mengikuti kebiasaan proyek ini
 * (lihat XlsxWriter). Yang dibutuhkan hanya satu himpunan karakter dan
 * penjumlahan sederhana — memasang pustaka untuk itu justru menambah beban.
 *
 * Keluarannya SVG supaya tetap tajam pada pencetak termal maupun laser, tidak
 * seperti gambar raster yang pecah saat diperbesar.
 */
class Code128
{
    /**
     * Lebar tiap elemen untuk 107 lambang Code 128.
     *
     * Tiap entri berisi enam angka: lebar batang dan spasi bergantian, dimulai
     * dari batang. Lambang terakhir (Stop) punya tujuh. Angka-angka ini baku
     * dan tidak boleh diutak-atik — pemindai mencocokkannya persis.
     */
    private const POLA = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213',
        '122312', '132212', '221213', '221312', '231212', '112232', '122132',
        '122231', '113222', '123122', '123221', '223211', '221132', '221231',
        '213212', '223112', '312131', '311222', '321122', '321221', '312212',
        '322112', '322211', '212123', '212321', '232121', '111323', '131123',
        '131321', '112313', '132113', '132311', '211313', '231113', '231311',
        '112133', '112331', '132131', '113123', '113321', '133121', '313121',
        '211331', '231131', '213113', '213311', '213131', '311123', '311321',
        '331121', '312113', '312311', '332111', '314111', '221411', '431111',
        '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114',
        '413111', '241112', '134111', '111242', '121142', '121241', '114212',
        '124112', '124211', '411212', '421112', '421211', '212141', '214121',
        '412121', '111143', '111341', '131141', '114113', '114311', '411113',
        '411311', '113141', '114131', '311141', '411131', '211412', '211214',
        '211232', '2331112',
    ];

    /** Lambang pembuka himpunan B — memuat seluruh huruf dan angka. */
    private const MULAI_B = 104;

    /** Lambang penutup. */
    private const BERHENTI = 106;

    /**
     * Mengubah teks menjadi deretan lambang Code 128, lengkap dengan angka
     * periksa dan lambang penutup.
     *
     * @return array<int> nomor lambang
     * @throws InvalidArgumentException bila ada karakter yang tidak didukung
     */
    public function lambang(string $teks): array
    {
        if ($teks === '') {
            throw new InvalidArgumentException('Teks barcode tidak boleh kosong.');
        }

        $lambang = [self::MULAI_B];
        $jumlah  = self::MULAI_B;
        $posisi  = 1;

        foreach (str_split($teks) as $huruf) {
            $kode = ord($huruf) - 32;

            // Himpunan B memuat karakter cetak ASCII dari spasi sampai DEL.
            if ($kode < 0 || $kode > 94) {
                throw new InvalidArgumentException(
                    'Karakter "' . $huruf . '" tidak bisa dimuat Code 128 himpunan B.'
                );
            }

            $lambang[] = $kode;

            // Angka periksa: tiap lambang dikalikan posisinya, lalu dijumlahkan.
            $jumlah += $kode * $posisi;
            $posisi++;
        }

        $lambang[] = $jumlah % 103;      // angka periksa
        $lambang[] = self::BERHENTI;

        return $lambang;
    }

    /**
     * Menggambar barcode sebagai SVG.
     *
     * @param  string $teks     nomor resi apa adanya
     * @param  int    $tinggi   tinggi batang dalam piksel
     * @param  float  $satuan   lebar satu modul; 2 sudah aman untuk pencetak 203 dpi
     */
    public function svg(string $teks, int $tinggi = 60, float $satuan = 2.0): string
    {
        $teks = strtoupper(trim($teks));

        $batang = [];
        $x = 0.0;

        foreach ($this->lambang($teks) as $lambang) {
            $pola = self::POLA[$lambang];
            $gelap = true;   // tiap lambang selalu dimulai dengan batang

            foreach (str_split($pola) as $lebarModul) {
                $lebar = (int) $lebarModul * $satuan;

                if ($gelap) {
                    $batang[] = '<rect x="' . round($x, 2) . '" y="0" width="'
                        . round($lebar, 2) . '" height="' . $tinggi . '"/>';
                }

                $x += $lebar;
                $gelap = ! $gelap;
            }
        }

        /*
         * Zona sunyi di kiri dan kanan. Tanpa ruang kosong ini pemindai tidak
         * bisa menemukan awal dan akhir barcode — kesalahan yang sering
         * membuat barcode tampak baik tetapi tidak pernah terbaca.
         */
        $sunyi = 10 * $satuan;
        $lebarTotal = $x + ($sunyi * 2);

        return '<svg xmlns="http://www.w3.org/2000/svg" '
            . 'width="100%" viewBox="0 0 ' . round($lebarTotal, 2) . ' ' . $tinggi . '" '
            . 'preserveAspectRatio="none" shape-rendering="crispEdges">'
            . '<rect width="100%" height="100%" fill="#fff"/>'
            . '<g fill="#000" transform="translate(' . round($sunyi, 2) . ',0)">'
            . implode('', $batang)
            . '</g></svg>';
    }

    /**
     * Apakah teks ini bisa dijadikan barcode?
     *
     * Dipakai tampilan untuk memutuskan menggambar barcode atau menampilkan
     * keterangan, alih-alih membiarkan halaman gagal.
     */
    public function bisa(?string $teks): bool
    {
        $teks = strtoupper(trim((string) $teks));

        if ($teks === '') {
            return false;
        }

        foreach (str_split($teks) as $huruf) {
            $kode = ord($huruf) - 32;

            if ($kode < 0 || $kode > 94) {
                return false;
            }
        }

        return true;
    }
}
