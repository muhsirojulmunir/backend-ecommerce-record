<?php

// Polyfill untuk server hosting yang belum mengaktifkan ekstensi PHP fileinfo
if (! class_exists('finfo')) {
    if (! defined('FILEINFO_NONE')) {
        define('FILEINFO_NONE', 0);
    }
    if (! defined('FILEINFO_MIME_TYPE')) {
        define('FILEINFO_MIME_TYPE', 16);
    }
    if (! defined('FILEINFO_MIME')) {
        define('FILEINFO_MIME', 1040);
    }

    class finfo
    {
        public function __construct($flags = null, $magicFile = null)
        {
        }

        public function file(string $filename, int $flags = FILEINFO_MIME_TYPE, $context = null): string|false
        {
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mimes = [
                'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                'gif' => 'image/gif', 'webp' => 'image/webp', 'svg' => 'image/svg+xml',
                'pdf' => 'application/pdf', 'zip' => 'application/zip',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'xls' => 'application/vnd.ms-excel', 'csv' => 'text/csv',
                'txt' => 'text/plain', 'json' => 'application/json',
                'mp4' => 'video/mp4', 'mov' => 'video/quicktime',
            ];

            return $mimes[$ext] ?? 'application/octet-stream';
        }

        public function buffer(string $string, int $flags = FILEINFO_MIME_TYPE, $context = null): string|false
        {
            return 'application/octet-stream';
        }
    }
}

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Percayai semua proxy (wajib di cPanel/shared hosting agar HTTPS bekerja)
        $middleware->trustProxies(at: '*');

        // Bebaskan route login dari CSRF token check agar tidak pernah error 419 saat login di hosting
        $middleware->validateCsrfTokens(except: [
            'admin/login',
            'login',
            '/',
        ]);

        // Harus berjalan sebelum pemeriksaan CSRF, sebab unggahan yang
        // melebihi post_max_size membuat token CSRF ikut hilang.
        $middleware->prependToGroup('web', \App\Http\Middleware\CekUkuranUnggahan::class);

        // Memperkecil foto besar sebelum validasi, agar admin tidak perlu
        // mengompres sendiri. Ditempatkan di akhir rantai supaya sesi dan
        // CSRF sudah diperiksa lebih dulu.
        $middleware->appendToGroup('web', \App\Http\Middleware\KompresUnggahanGambar::class);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'log.admin' => \App\Http\Middleware\LogAdminActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
