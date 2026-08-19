<?php

namespace App\Http\Middleware;

use App\Support\KompresGambar;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memperkecil foto yang terlalu besar sebelum permintaan diteruskan.
 */
class KompresUnggahanGambar
{
    /** Jenis berkas yang diproses. */
    private const JENIS_GAMBAR = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Sengaja memakai files->all(), bukan allFiles().
        // allFiles() menyimpan hasil konversinya di properti convertedFiles,
        // sehingga penggantian berkas sesudahnya tidak akan terbaca lagi.
        $berkas = $request->files->all();

        if (! empty($berkas)) {
            $request->files->replace($this->proses($berkas));

            // Bersihkan memo bila sudah terlanjur terisi oleh proses lain,
            // agar controller menerima berkas yang sudah diperkecil.
            $this->kosongkanMemo($request);
        }

        return $next($request);
    }

    /**
     * Hapus cache konversi berkas milik Request (properti terlindung).
     */
    private function kosongkanMemo(Request $request): void
    {
        Closure::bind(function () {
            $this->convertedFiles = null;
        }, $request, Request::class)();
    }

    /**
     * Telusuri struktur berkas yang bisa bersarang (mis. new_images[0],
     * color_images[2]) dan perkecil setiap gambar yang melewati batas.
     */
    private function proses(array $berkas): array
    {
        foreach ($berkas as $kunci => $isi) {
            if (is_array($isi)) {
                $berkas[$kunci] = $this->proses($isi);
                continue;
            }

            if ($isi instanceof UploadedFile && $this->perluDiperkecil($isi)) {
                $berkas[$kunci] = KompresGambar::perkecil($isi);
            }
        }

        return $berkas;
    }

    private function perluDiperkecil(UploadedFile $berkas): bool
    {
        if (! $berkas->isValid()) {
            return false;
        }

        if ($berkas->getSize() <= KompresGambar::BATAS_BYTE) {
            return false;
        }

        // getMimeType() membaca isi berkas, bukan sekadar percaya nama/tipe
        // yang dikirim browser
        return in_array(strtolower($berkas->getMimeType() ?? ''), self::JENIS_GAMBAR, true);
    }
}
