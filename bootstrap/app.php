<?php

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
        // Percayai semua proxy (wajib di cPanel/shared hosting agar CSRF & HTTPS bekerja)
        $middleware->trustProxies(at: '*');

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
