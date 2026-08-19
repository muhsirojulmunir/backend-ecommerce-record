<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menangkap unggahan yang melebihi post_max_size PHP.
 *
 * Saat batas itu terlampaui, PHP membuang seluruh isi POST — termasuk token
 * CSRF dan seluruh berkas. Akibatnya permintaan gagal di pemeriksaan CSRF
 * dan pengguna hanya melihat galat 403/419 tanpa penjelasan.
 *
 * Middleware ini dijalankan sebelum pemeriksaan CSRF, mengenali kondisi
 * tersebut, lalu mengembalikan pesan yang bisa dimengerti.
 */
class CekUkuranUnggahan
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->melebihiBatas($request)) {
            $batas = $this->batasPostMb();

            $pesan = "Total ukuran berkas yang diunggah melebihi batas server ({$batas}). "
                . 'Coba unggah lebih sedikit foto sekaligus, atau perkecil ukuran fotonya terlebih dahulu.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $pesan], 413);
            }

            return redirect()->back()->with('error', $pesan);
        }

        return $next($request);
    }

    /**
     * Ciri khas post_max_size terlampaui: browser mengirim isi (Content-Length
     * besar) tetapi PHP menerima $_POST dan $_FILES dalam keadaan kosong.
     */
    private function melebihiBatas(Request $request): bool
    {
        if (! $request->isMethod('POST')) {
            return false;
        }

        $panjang = (int) $request->server('CONTENT_LENGTH', 0);

        if ($panjang <= 0) {
            return false;
        }

        $batas = $this->keBytes(ini_get('post_max_size'));

        // Batas 0 berarti tidak dibatasi
        if ($batas <= 0) {
            return false;
        }

        return $panjang > $batas && empty($_POST) && empty($_FILES);
    }

    private function batasPostMb(): string
    {
        $bytes = $this->keBytes(ini_get('post_max_size'));

        return $bytes > 0 ? round($bytes / 1048576) . 'MB' : 'tidak diketahui';
    }

    /**
     * Ubah notasi ukuran PHP ("8M", "512K", "1G") menjadi jumlah byte.
     */
    private function keBytes(?string $nilai): int
    {
        $nilai = trim((string) $nilai);

        if ($nilai === '') {
            return 0;
        }

        $angka = (int) $nilai;
        $satuan = strtolower(substr($nilai, -1));

        return match ($satuan) {
            'g'     => $angka * 1024 * 1024 * 1024,
            'm'     => $angka * 1024 * 1024,
            'k'     => $angka * 1024,
            default => $angka,
        };
    }
}
