<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pelacakan paket lewat Biteship.
 *
 * Dipakai untuk DUA arah sekaligus:
 *
 *   1. Paket keluar  — barang dari toko ke pembeli.
 *   2. Paket kembali — barang pengembalian dari pembeli ke toko.
 *
 * Arah kedua itulah alasan layanan ini memakai alamat "public tracking"
 * (/v1/trackings/{resi}/couriers/{kode}), bukan /v1/trackings/{resi} yang
 * dipakai halaman pembeli. Alamat publik bisa melacak resi APA PUN, termasuk
 * yang tidak dipesan lewat Biteship — dan resi pengembalian memang dibeli
 * sendiri oleh pembeli di gerai kurir, bukan lewat sistem kita.
 *
 * Sumber: https://biteship.com/en/docs/api/trackings/retrieve_public
 */
class PelacakanService
{
    /**
     * Menerjemahkan nama kurir yang ditulis bebas menjadi kode yang dikenal
     * Biteship.
     *
     * Diperlukan karena kolom kurir di basis data berisi teks apa adanya —
     * "JNE Reguler (REG)", "J&T Express", "jne", bahkan kadang pembeli
     * mengetikkan nomor resinya ke kolom kurir. Tanpa penerjemah ini,
     * permintaan ke Biteship akan selalu ditolak.
     *
     * Urutannya penting: yang lebih khusus diperiksa lebih dulu, sebab
     * "jnt" mengandung "jn" dan bisa tertukar dengan "jne".
     */
    public const KODE_KURIR = [
        'jnt'       => ['j&t', 'jnt', 'j & t'],
        'jne'       => ['jne'],
        'sicepat'   => ['sicepat', 'si cepat'],
        'anteraja'  => ['anteraja', 'anter aja'],
        'pos'       => ['pos indonesia', 'pos ind', 'posind'],
        'tiki'      => ['tiki'],
        'ninja'     => ['ninja'],
        'lion'      => ['lion'],
        'idexpress' => ['id express', 'idexpress', 'ide'],
        'sap'       => ['sap'],
        'wahana'    => ['wahana'],
        'rpx'       => ['rpx'],
        'gojek'     => ['gojek', 'gosend', 'go-send'],
        'grab'      => ['grab'],
        'paxel'     => ['paxel'],
        'lalamove'  => ['lalamove'],
        'spx'       => ['spx', 'shopee express'],
    ];

    /**
     * Lima tahap yang ditampilkan sebagai batang bergambar.
     *
     * Biteship memakai 14 status, terlalu rinci untuk ditampilkan sebagai
     * batang — dan sebagiannya (rejected, disposed, cancelled) bukan tahap
     * melainkan kegagalan. Keempat belasnya diringkas ke lima tahap yang
     * benar-benar dilalui paket normal, sisanya ditandai terpisah.
     */
    public const TAHAP = [
        ['kode' => 'confirmed',  'label' => 'Dikonfirmasi', 'ikon' => 'fa-clipboard-check'],
        ['kode' => 'picked',     'label' => 'Dijemput',     'ikon' => 'fa-box'],
        ['kode' => 'inTransit',  'label' => 'Perjalanan',   'ikon' => 'fa-truck'],
        ['kode' => 'droppingOff','label' => 'Diantar',      'ikon' => 'fa-truck-fast'],
        ['kode' => 'delivered',  'label' => 'Diterima',     'ikon' => 'fa-circle-check'],
    ];

    /** Status Biteship yang menandakan perjalanan gagal, bukan tahap normal. */
    public const STATUS_GAGAL = [
        'rejected'        => 'Ditolak penerima',
        'courierNotFound' => 'Kurir tidak ditemukan',
        'returned'        => 'Dikembalikan ke pengirim',
        'cancelled'       => 'Dibatalkan',
        'disposed'        => 'Dimusnahkan',
        'onHold'          => 'Tertahan',
    ];

    /**
     * Tahap keberapa paket ini sekarang (0-4), atau -1 bila belum diketahui.
     *
     * Status yang tidak persis sama dengan patokan tetap dipetakan ke tahap
     * terdekat — "allocated" dan "pickingUp" misalnya, keduanya masih dalam
     * rangkaian penjemputan.
     */
    public function tahapDari(?string $status): int
    {
        $peta = [
            'confirmed'       => 0,
            'allocated'       => 0,
            'pickingUp'       => 1,
            'picked'          => 1,
            'inTransit'       => 2,
            'returnInTransit' => 2,
            'droppingOff'     => 3,
            'delivered'       => 4,
        ];

        return $peta[$status] ?? -1;
    }

    /**
     * Menerjemahkan pesan galat Biteship ke bahasa yang bisa ditindaklanjuti.
     *
     * Pesan aslinya berbahasa Inggris dan menyebut istilah teknis. Yang paling
     * sering muncul — saldo habis — bahkan tidak jelas maksudnya bagi yang
     * belum tahu bahwa Biteship menagih per panggilan, bukan bulanan. Di sini
     * pesannya diganti dengan yang menyebutkan apa yang harus dilakukan.
     */
    private function terjemahkanGalat(?string $asli): string
    {
        if (blank($asli)) {
            return 'Kurir belum punya catatan untuk resi ini. Biasanya baru muncul '
                 . 'beberapa jam setelah paket diserahkan ke kurir.';
        }

        $kecil = strtolower($asli);

        if (str_contains($kecil, 'balance')) {
            return 'Saldo Biteship habis, jadi pelacakan tidak bisa dijalankan. '
                 . 'Isi ulang saldo di dashboard Biteship — pelacakan ditagih '
                 . 'per panggilan, bukan langganan bulanan.';
        }

        if (str_contains($kecil, 'not found') || str_contains($kecil, 'invalid waybill')) {
            return 'Resi ini tidak dikenali kurir. Periksa lagi nomornya, atau '
                 . 'tunggu beberapa jam bila paket baru saja diserahkan.';
        }

        if (str_contains($kecil, 'courier')) {
            return 'Kurir yang dipilih tidak cocok dengan nomor resinya. '
                 . 'Pastikan nama kurirnya benar.';
        }

        // Pesan yang belum dikenali diteruskan apa adanya — lebih berguna
        // daripada diganti kalimat umum yang menyembunyikan sebabnya.
        return $asli;
    }

    public function kodeKurir(?string $teks): ?string
    {
        if (blank($teks)) {
            return null;
        }

        $bersih = strtolower(trim($teks));

        foreach (self::KODE_KURIR as $kode => $kataKunci) {
            foreach ($kataKunci as $kata) {
                if (str_contains($bersih, $kata)) {
                    return $kode;
                }
            }
        }

        return null;
    }

    /**
     * Mengambil riwayat perjalanan satu paket.
     *
     * Selalu mengembalikan larik dengan bentuk yang sama, baik berhasil
     * maupun gagal — pemanggilnya tidak perlu menebak-nebak. Kegagalan
     * menghubungi Biteship BUKAN pengecualian yang dilempar: paket yang
     * belum terlacak adalah keadaan yang wajar, bukan kesalahan program.
     */
    public function lacak(?string $resi, ?string $kurirTeks): array
    {
        $kosong = [
            'ok'       => false,
            'pesan'    => '',
            'status'   => null,
            'kurir'    => null,
            'resi'     => $resi,
            'riwayat'  => [],
            'tahap'    => -1,
            'gagal'    => null,
            'tautan'   => null,
            'kurir_nama' => null,
            'kurir_hp'   => null,
            'kurir_plat' => null,
        ];

        if (blank($resi)) {
            return $kosong + [];
        }

        $kode = $this->kodeKurir($kurirTeks);

        if (! $kode) {
            $kosong['pesan'] = 'Kurirnya tidak dikenali dari teks "' . $kurirTeks . '", '
                . 'jadi paketnya tidak bisa dilacak otomatis. Cek langsung di situs kurirnya.';
            return $kosong;
        }

        $kunci = env('BITESHIP_API_KEY');

        if (blank($kunci)) {
            $kosong['pesan'] = 'Kunci Biteship belum diatur, pelacakan otomatis tidak aktif.';
            return $kosong;
        }

        /*
         * Hasilnya disinggahkan beberapa menit.
         *
         * Kurir memperbarui riwayat paling cepat hitungan jam, jadi menanyakan
         * ulang tiap kali halaman dibuka hanya membuang kuota dan memperlambat
         * halaman. Lima menit cukup terasa segar bagi admin.
         */
        $sandi = 'lacak:' . $kode . ':' . $resi;

        return Cache::remember($sandi, now()->addMinutes(5), function () use ($resi, $kode, $kunci, $kosong) {
            try {
                $respons = Http::timeout(8)
                    ->withHeaders(['Authorization' => 'Bearer ' . $kunci])
                    ->get(rtrim(env('BITESHIP_API_URL', 'https://api.biteship.com/v1'), '/')
                        . '/trackings/' . urlencode($resi) . '/couriers/' . $kode);

                $data = $respons->json();

                if (! $respons->successful() || empty($data['success'])) {
                    $kosong['pesan'] = $this->terjemahkanGalat($data['error'] ?? null);
                    return $kosong;
                }

                $riwayat = collect($data['history'] ?? [])
                    ->map(fn ($h) => [
                        'waktu'      => $h['updated_at'] ?? $h['updated_time'] ?? null,
                        'status'     => $h['status'] ?? '',
                        'keterangan' => $h['note'] ?? '',
                    ])
                    // Terbaru di atas: yang paling ingin diketahui admin
                    // adalah posisi paket SEKARANG, bukan riwayat awalnya.
                    ->reverse()
                    ->values()
                    ->all();

                $status = $data['status'] ?? null;

                return [
                    'ok'      => true,
                    'pesan'   => '',
                    'status'  => $status,
                    'kurir'   => $data['courier']['company'] ?? $kode,
                    'resi'    => $data['waybill_id'] ?? $resi,
                    'riwayat' => $riwayat,

                    // Untuk batang tahapan bergambar.
                    'tahap'      => $this->tahapDari($status),
                    'gagal'      => self::STATUS_GAGAL[$status] ?? null,

                    // Halaman pelacakan milik Biteship. Membukanya tidak
                    // memotong saldo lagi, jadi admin maupun pembeli bisa
                    // memeriksa sepuasnya di sana.
                    'tautan'     => $data['link'] ?? null,

                    // Nama dan pelat kurir: satu-satunya keterangan yang bisa
                    // dipegang bila paket bermasalah di tangan kurir.
                    'kurir_nama' => $data['courier']['driver_name'] ?? null,
                    'kurir_hp'   => $data['courier']['driver_phone'] ?? null,
                    'kurir_plat' => $data['courier']['driver_plate_number'] ?? null,
                ];
            } catch (\Throwable $e) {
                Log::warning('Pelacakan Biteship gagal', [
                    'resi' => $resi, 'kurir' => $kode, 'pesan' => $e->getMessage(),
                ]);

                $kosong['pesan'] = 'Tidak bisa menghubungi layanan pelacakan. Coba lagi sebentar lagi.';
                return $kosong;
            }
        });
    }
}
