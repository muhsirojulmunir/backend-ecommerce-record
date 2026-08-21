<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pelacakan paket lewat Biteship.
 */
class PelacakanService
{
    /**
 * Menerjemahkan nama kurir yang ditulis bebas menjadi kode yang dikenal Biteship.
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

        /*
         * Resi yang pernah dikarang sistem sendiri.
         *
         * Versi lama menambal kegagalan Biteship dengan nomor buatan berawalan
         * "REC-", "REC0", atau "RTR-BITESHIP-". Nomor itu tidak pernah terdaftar
         * di kurir mana pun, jadi melacaknya hanya menghasilkan galat yang
         * membingungkan — dan tetap memotong saldo Biteship Rp 10 tiap kali
         * dicoba. Lebih baik dikenali di sini dan dijelaskan apa adanya.
         */
        if (preg_match('/^(REC-|REC0|RTR-BITESHIP-)/i', trim($resi))) {
            $kosong['pesan'] = 'Nomor ini bukan resi dari kurir — dulu diterbitkan sistem '
                . 'sendiri saat pemesanan ke Biteship gagal. Artinya pengiriman ini belum '
                . 'pernah dipesan ke kurir. Pesan ulang pengirimannya, atau isi nomor resi '
                . 'yang sebenarnya bila paketnya diantar dengan cara lain.';

            return $kosong;
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

        // Hasilnya disinggahkan beberapa menit.
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
