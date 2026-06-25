<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SetAdminLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The application may run behind HTTPS reverse proxies such as ngrok.
        // Trust forwarded scheme/host headers so generated links and Vite assets
        // use the public HTTPS origin instead of the proxy's local HTTP origin.
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: ['payment/return/*', 'payment/webhook/*']);
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'admin.locale' => SetAdminLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
