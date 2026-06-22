<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer('storefront.partials.header', function ($view): void {
            $request = request();
            $service = app(CartService::class);
            $view->with('cartCount', $service->count($service->currentCart($request)));
        });
    }
}
