<?php

namespace App\Http\Middleware;

use App\Services\LanguageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = app(LanguageService::class)->getDefault()?->code;

        app()->setLocale($locale ?? config('app.locale'));

        return $next($request);
    }
}
