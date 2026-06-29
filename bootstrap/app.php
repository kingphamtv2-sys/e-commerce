<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CustomerMiddleware;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetAdminLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::get('/health', fn () => response()->json(['status' => 'ok']))
                ->name('health');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        // The application may run behind HTTPS reverse proxies such as ngrok.
        // Trust forwarded scheme/host headers so generated links and Vite assets
        // use the public HTTPS origin instead of the proxy's local HTTP origin.
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES', env('APP_ENV', 'production') === 'production' ? '127.0.0.1' : '*'),
        );
        $middleware->validateCsrfTokens(except: ['payment/return/*', 'payment/webhook/*']);
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'admin.locale' => SetAdminLocale::class,
            'customer' => CustomerMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
