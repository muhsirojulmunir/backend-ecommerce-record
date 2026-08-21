<?php

namespace App\Services;

use App\Models\BiteshipSaldo;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Memantau saldo Biteship.
 *
 * Biteship tidak menyediakan endpoint saldo — sudah diperiksa langsung ke
 * API-nya dan semua kandidat alamat menjawab "Route not found". Karena itu
 * pemantauannya dibangun dari dua sumber yang saling melengkapi:
 *
 *   1. PERKIRAAN — dari saldo yang dicatat admin saat mengisi ulang, dikurangi
 *      ongkir semua pengiriman yang terbit sesudahnya. Gunanya memperingatkan
 *      LEBIH AWAL, sebelum saldonya benar-benar habis. Sifatnya kira-kira.
 *
 *   2. KEPASTIAN — dari penolakan Biteship yang sesungguhnya. Kalau API
 *      menjawab saldo tidak cukup, itu fakta, bukan dugaan. Penanda ini
 *      mengalahkan perkiraan apa pun.
 *
 * Perkiraan sengaja tidak menghitung biaya kecil per panggilan (Tracking
 * Rp 10, Rates Rp 5, Maps Rp 2), sehingga saldo sebenarnya selalu sedikit
 * LEBIH RENDAH daripada yang ditampilkan. Arah kesalahannya dipilih begitu
 * dengan sengaja: lebih baik memperingatkan terlalu cepat daripada terlambat.
 */
class SaldoBiteshipService
{
    private const KUNCI_HABIS = 'biteship.saldo-habis';

    /**
     * Catatan saldo terakhir yang dituliskan admin.
     *
     * Diurutkan juga berdasarkan id, bukan hanya waktu: dua catatan yang
     * tertulis pada detik yang sama punya `dicatat_pada` identik, dan tanpa
     * pemecah seri MySQL bebas memilih salah satunya. Yang benar selalu yang
     * ditulis paling akhir.
     */
    public function catatanTerakhir(): ?BiteshipSaldo
    {
        return BiteshipSaldo::with('pencatat')
            ->orderByDesc('dicatat_pada')
            ->orderByDesc('id')
            ->first();
    }

    /** Menuliskan saldo yang sedang tertera di dasbor Biteship. */
    public function catat(int $saldo, ?string $catatan = null): BiteshipSaldo
    {
        // Mencatat saldo baru berarti admin baru saja melihat dasbornya.
        // Penanda "habis" dari sebelumnya sudah tidak berlaku lagi.
        $this->tandaiPulih();

        return BiteshipSaldo::create([
            'saldo_tercatat' => max(0, $saldo),
            'dicatat_pada'   => now(),
            'dicatat_oleh'   => Auth::id(),
            'catatan'        => $catatan,
        ]);
    }

    /**
     * Ongkir yang sudah terpakai sejak saldo terakhir dicatat.
     *
     * Yang dihitung hanya pesanan yang benar-benar punya nomor resi dari
     * Biteship. Pesanan yang gagal terbit resinya tidak memotong saldo, jadi
     * tidak boleh ikut dikurangkan.
     */
    public function terpakaiSejakDicatat(): int
    {
        $catatan = $this->catatanTerakhir();

        if (! $catatan) {
            return 0;
        }

        return (int) Order::where('status', 'shipped')
            ->whereNotNull('tracking_number')
            ->where('tracking_number', 'not like', 'REC-%')   // resi cadangan, bukan dari Biteship
            ->where('updated_at', '>=', $catatan->dicatat_pada)
            ->sum('shipping_actual_cost');
    }

    /** Perkiraan sisa saldo, atau null bila belum pernah dicatat. */
    public function perkiraan(): ?int
    {
        $catatan = $this->catatanTerakhir();

        if (! $catatan) {
            return null;
        }

        return max(0, $catatan->saldo_tercatat - $this->terpakaiSejakDicatat());
    }

    /** Menandai bahwa Biteship benar-benar menolak karena saldo kurang. */
    public function tandaiHabis(?string $pesan = null): void
    {
        Cache::put(self::KUNCI_HABIS, [
            'pada'  => now()->toDateTimeString(),
            'pesan' => $pesan,
        ], now()->addMinutes((int) config('biteship.menit_tanda_habis', 180)));
    }

    /** Menghapus penanda — dipanggil begitu ada pengiriman yang berhasil. */
    public function tandaiPulih(): void
    {
        Cache::forget(self::KUNCI_HABIS);
    }

    public function sedangHabis(): bool
    {
        return Cache::has(self::KUNCI_HABIS);
    }

    /**
     * Ringkasan keadaan saldo untuk ditampilkan di halaman admin.
     *
     * @return array{nada: string, judul: string, pesan: string, perkiraan: ?int, dicatat: ?\Illuminate\Support\Carbon}
     */
    public function ringkasan(): array
    {
        $catatan   = $this->catatanTerakhir();
        $perkiraan = $this->perkiraan();
        $ambang    = (int) config('biteship.ambang_peringatan', 10_000);

        $rupiah = fn (int $n) => 'Rp ' . number_format($n, 0, ',', '.');

        // Fakta mengalahkan perkiraan.
        if ($this->sedangHabis()) {
            return [
                'nada'      => 'bahaya',
                'judul'     => 'Saldo Biteship habis',
                'pesan'     => 'Biteship menolak permintaan terakhir karena saldo tidak cukup. '
                    . 'Resi tidak bisa terbit dan pesanan tidak bisa dikirim sampai saldo diisi.',
                'perkiraan' => $perkiraan,
                'dicatat'   => $catatan?->dicatat_pada,
            ];
        }

        if ($catatan === null) {
            return [
                'nada'      => 'diam',
                'judul'     => 'Saldo Biteship belum dicatat',
                'pesan'     => 'Catat saldo yang tertera di dasbor Biteship supaya sistem bisa '
                    . 'memperingatkan sebelum saldonya habis.',
                'perkiraan' => null,
                'dicatat'   => null,
            ];
        }

        if ($perkiraan !== null && $perkiraan <= $ambang) {
            return [
                'nada'      => 'awas',
                'judul'     => 'Saldo Biteship menipis',
                'pesan'     => 'Perkiraan sisa saldo tinggal ' . $rupiah($perkiraan)
                    . ', di bawah batas ' . $rupiah($ambang) . '. Segera isi ulang '
                    . 'sebelum ada pesanan yang gagal dikirim.',
                'perkiraan' => $perkiraan,
                'dicatat'   => $catatan->dicatat_pada,
            ];
        }

        return [
            'nada'      => 'aman',
            'judul'     => 'Saldo Biteship',
            'pesan'     => 'Perkiraan sisa ' . $rupiah((int) $perkiraan) . '.',
            'perkiraan' => $perkiraan,
            'dicatat'   => $catatan->dicatat_pada,
        ];
    }

    /**
     * Apakah pesan galat dari Biteship menandakan saldo tidak cukup?
     */
    public function galatSoalSaldo(?string $pesan, mixed $kode = null): bool
    {
        $pesan = strtolower((string) $pesan);

        return str_contains($pesan, 'sufficient balance')
            || str_contains($pesan, 'insufficient')
            || str_contains($pesan, 'saldo')
            || (string) $kode === '40001001';
    }
}
