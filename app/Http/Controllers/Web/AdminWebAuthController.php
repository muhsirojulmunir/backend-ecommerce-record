<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AdminWebAuthController extends Controller
{
    /** Banyaknya percobaan masuk yang gagal sebelum dijeda. */
    private const BATAS_PERCOBAAN = 5;

    /** Lamanya jeda setelah batas terlampaui, dalam detik. */
    private const JEDA_DETIK = 900;

    /**
     * Tentukan halaman pertama sesuai permission user.
     */
    private function redirectAfterLogin($user): string
    {
        if ($user->can('view dashboard')) {
            return route('admin.dashboard');
        }
        if ($user->can('manage orders')) {
            return route('admin.orders');
        }
        if ($user->can('manage products')) {
            return route('admin.products');
        }
        return route('admin.products');
    }

    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect($this->redirectAfterLogin(Auth::user()));
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        /*
         * Percobaan masuk dibatasi.
         *
         * Halaman ini menjaga seluruh isi toko — pesanan, saldo R_Pay, dan
         * antrean pencairan. Tanpa pembatas, siapa pun bisa menebak kata sandi
         * admin sebanyak-banyaknya tanpa hambatan apa pun. Halaman masuk sisi
         * pembeli sudah punya pembatas bawaan Laravel; yang ini belum.
         *
         * Kuncinya memakai email DAN alamat IP, supaya penyerang tidak bisa
         * mengunci akun admin dari luar hanya dengan salah memasukkan sandi
         * berulang kali.
         */
        $kunci = 'admin-masuk|' . strtolower($credentials['email']) . '|' . $request->ip();

        /*
         * Pembatas ini bersandar pada penyimpanan cache. Bila cache-nya bermasalah
         * — misalnya CACHE_STORE diarahkan ke basis data sementara tabel `cache`
         * belum termigrasi di server — pemanggilannya melempar galat.
         *
         * Galat itu tidak boleh menjatuhkan login. Pembatas percobaan adalah
         * lapisan tambahan; kalau lapisannya sendiri rusak, yang benar adalah
         * mencatatnya lalu tetap memeriksa kata sandi, bukan mengunci admin di
         * luar tokonya sendiri dengan layar 500.
         */
        try {
            if (RateLimiter::tooManyAttempts($kunci, self::BATAS_PERCOBAAN)) {
                $detik = RateLimiter::availableIn($kunci);

                throw ValidationException::withMessages([
                    'email' => ['Terlalu banyak percobaan masuk. Coba lagi dalam '
                        . ceil($detik / 60) . ' menit.'],
                ]);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::warning('Pembatas percobaan masuk tidak bisa dipakai, login diteruskan', [
                'sebab' => $e->getMessage(),
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if ($user->isAdmin()) {
                $this->catatAman(fn () => RateLimiter::clear($kunci));
                $request->session()->regenerate();
                return redirect($this->redirectAfterLogin($user));
            }

            // Bukan admin → tolak. Percobaannya tetap dihitung: kata sandi yang
            // benar pada akun tanpa hak akses tetap menandakan seseorang sedang
            // mencari-cari pintu masuk.
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $this->catatAman(fn () => RateLimiter::hit($kunci, self::JEDA_DETIK));

            throw ValidationException::withMessages([
                'email' => ['Akses ditolak. Halaman ini hanya untuk Administrator.'],
            ]);
        }

        $this->catatAman(fn () => RateLimiter::hit($kunci, self::JEDA_DETIK));

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Menjalankan pencatatan percobaan tanpa membuat halaman jatuh bila
     * penyimpanan cache-nya sedang bermasalah.
     */
    private function catatAman(callable $tindakan): void
    {
        try {
            $tindakan();
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat percobaan masuk', ['sebab' => $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 'login' = nama route halaman login (bukan admin.login)
        return redirect()->route('login');
    }
}
