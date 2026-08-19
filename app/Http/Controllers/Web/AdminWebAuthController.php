<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminWebAuthController extends Controller
{
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

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if ($user->isAdmin()) {
                $request->session()->regenerate();
                return redirect($this->redirectAfterLogin($user));
            }

            // Bukan admin → tolak
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Akses ditolak. Halaman ini hanya untuk Administrator.'],
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
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
