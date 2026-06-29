<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\ThemeSettingService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') && config('app.debug')) {
            throw new RuntimeException('APP_DEBUG must be false in production.');
        }

        View::composer('storefront.partials.header', function ($view): void {
            $request = request();
            $service = app(CartService::class);
            $view->with('cartCount', $service->count($service->currentCart($request)));
        });

        View::composer('layouts.public', function ($view): void {
            $theme = app(ThemeSettingService::class);
            $view->with([
                'frontendTheme' => $theme->all(),
                'frontendThemeService' => $theme,
                'frontendThemeCssVariables' => $theme->cssVariables(),
            ]);
        });
    }
}
