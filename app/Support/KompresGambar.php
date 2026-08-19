<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Memperkecil foto yang diunggah agar tidak melebihi batas ukuran.
 *
 * Alurnya: bila berkas sudah di bawah batas, dibiarkan apa adanya. Bila
 * melebihi, sisi terpanjangnya diperkecil lalu disimpan ulang sebagai JPEG
 * dengan mutu yang diturunkan bertahap sampai ukurannya cukup.
 *
 * Memakai ekstensi GD yang sudah bawaan PHP, jadi tidak perlu paket tambahan.
 */
class KompresGambar
{
    /**
     * Batas ukuran hasil akhir dalam byte.
     *
     * Sengaja 1,9MB — sedikit di bawah batas validasi 2MB, supaya hasil
     * kompresi tidak pernah menyentuh batas dan ditolak karena selisih
     * pembulatan beberapa kilobyte.
     */
    public const BATAS_BYTE = 1945600;   // 1,9MB

    /** Sisi terpanjang maksimum setelah diperkecil. */
    private const SISI_MAKS = 1600;

    /** Mutu JPEG yang dicoba berurutan sampai ukurannya muat. */
    private const TINGKAT_MUTU = [82, 72, 62, 52, 42];

    /**
     * Kembalikan berkas yang sudah dijamin di bawah batas.
     *
     * Bila gambar tidak bisa diproses (format aneh, GD tidak aktif, dsb),
     * berkas aslinya dikembalikan tanpa perubahan — biarkan validasi
     * Laravel yang memutuskan.
     */
    public static function perkecil(UploadedFile $berkas, int $batas = self::BATAS_BYTE): UploadedFile
    {
        if (! $berkas->isValid()) {
            return $berkas;
        }

        // Sudah cukup kecil, tidak perlu disentuh
        if ($berkas->getSize() <= $batas) {
            return $berkas;
        }

        if (! extension_loaded('gd')) {
            return $berkas;
        }

        try {
            $sumber = self::bacaGambar($berkas->getRealPath());

            if (! $sumber) {
                return $berkas;
            }

            $sumber = self::luruskanOrientasi($sumber, $berkas->getRealPath(), $berkas->getMimeType());
            $kecil  = self::kecilkan($sumber);

            if ($kecil !== $sumber) {
                imagedestroy($sumber);
            }

            $tujuan = tempnam(sys_get_temp_dir(), 'kompres_') . '.jpg';

            foreach (self::TINGKAT_MUTU as $mutu) {
                imagejpeg($kecil, $tujuan, $mutu);

                if (filesize($tujuan) <= $batas) {
                    break;
                }
            }

            // Masih kebesaran walau mutu sudah paling rendah: perkecil lagi
            $putaran = 0;
            while (filesize($tujuan) > $batas && $putaran < 3) {
                $lebihKecil = imagescale($kecil, (int) (imagesx($kecil) * 0.75));

                if (! $lebihKecil) {
                    break;
                }

                imagedestroy($kecil);
                $kecil = $lebihKecil;
                imagejpeg($kecil, $tujuan, end(self::TINGKAT_MUTU) ?: 42);
                $putaran++;
            }

            imagedestroy($kecil);

            if (! file_exists($tujuan) || filesize($tujuan) === 0) {
                @unlink($tujuan);
                return $berkas;
            }

            $namaAsli = pathinfo($berkas->getClientOriginalName(), PATHINFO_FILENAME);

            // test: true — berkas dibuat sendiri, bukan hasil unggahan HTTP,
            // sehingga pemeriksaan is_uploaded_file() perlu dilewati.
            // Memakai kelas Illuminate agar helper seperti store() tetap tersedia.
            return new \Illuminate\Http\UploadedFile(
                $tujuan,
                $namaAsli . '.jpg',
                'image/jpeg',
                null,
                true
            );
        } catch (\Throwable $e) {
            Log::warning('Gagal memperkecil gambar: ' . $e->getMessage());

            return $berkas;
        }
    }

    /**
     * Perkecil banyak berkas sekaligus. Kunci array dipertahankan.
     *
     * @param  array<mixed, UploadedFile|null>  $daftar
     * @return array<mixed, UploadedFile|null>
     */
    public static function perkecilBanyak(array $daftar, int $batas = self::BATAS_BYTE): array
    {
        foreach ($daftar as $kunci => $berkas) {
            if ($berkas instanceof UploadedFile) {
                $daftar[$kunci] = self::perkecil($berkas, $batas);
            }
        }

        return $daftar;
    }

    // ─── Helper privat ────────────────────────────────────────────────────────

    /**
     * Baca berkas menjadi sumber daya gambar GD sesuai jenisnya.
     */
    private static function bacaGambar(string $path): \GdImage|false
    {
        $info = @getimagesize($path);

        if (! $info) {
            return false;
        }

        return match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            IMAGETYPE_GIF  => @imagecreatefromgif($path),
            default        => false,
        };
    }

    /**
     * Foto dari kamera ponsel sering menyimpan arah gambar di data EXIF.
     * Tanpa diluruskan, hasil kompresi bisa terbalik atau miring.
     */
    private static function luruskanOrientasi(\GdImage $gambar, string $path, ?string $mime = null): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $gambar;
        }

        // Data EXIF hanya ada pada JPEG. Memanggilnya untuk PNG/WebP
        // memunculkan peringatan "File not supported" di log.
        if (! in_array(strtolower((string) $mime), ['image/jpeg', 'image/jpg'], true)) {
            return $gambar;
        }

        $exif = @exif_read_data($path);
        $arah = $exif['Orientation'] ?? null;

        $derajat = match ($arah) {
            3       => 180,
            6       => -90,
            8       => 90,
            default => 0,
        };

        if ($derajat === 0) {
            return $gambar;
        }

        $diputar = imagerotate($gambar, $derajat, 0);

        if (! $diputar) {
            return $gambar;
        }

        imagedestroy($gambar);

        return $diputar;
    }

    /**
     * Perkecil gambar bila sisi terpanjangnya melebihi batas,
     * sambil menjaga perbandingan lebar dan tinggi.
     */
    private static function kecilkan(\GdImage $gambar): \GdImage
    {
        $lebar  = imagesx($gambar);
        $tinggi = imagesy($gambar);
        $sisi   = max($lebar, $tinggi);

        if ($sisi <= self::SISI_MAKS) {
            return self::ratakanLatar($gambar);
        }

        $skala      = self::SISI_MAKS / $sisi;
        $lebarBaru  = max(1, (int) round($lebar * $skala));

        $hasil = imagescale($gambar, $lebarBaru);

        return $hasil ? self::ratakanLatar($hasil) : self::ratakanLatar($gambar);
    }

    /**
     * JPEG tidak mengenal transparansi. Gambar PNG/WebP yang punya bagian
     * tembus pandang diberi latar putih dulu agar tidak menjadi hitam pekat.
     */
    private static function ratakanLatar(\GdImage $gambar): \GdImage
    {
        $lebar  = imagesx($gambar);
        $tinggi = imagesy($gambar);

        $kanvas = imagecreatetruecolor($lebar, $tinggi);

        if (! $kanvas) {
            return $gambar;
        }

        $putih = imagecolorallocate($kanvas, 255, 255, 255);
        imagefilledrectangle($kanvas, 0, 0, $lebar, $tinggi, $putih);
        imagecopy($kanvas, $gambar, 0, 0, 0, 0, $lebar, $tinggi);
        imagedestroy($gambar);

        return $kanvas;
    }
}
