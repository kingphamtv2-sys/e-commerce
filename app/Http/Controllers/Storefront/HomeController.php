<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Product;
use App\Services\BannerService;
use App\Services\CatalogService;
use App\Services\CurrencyService;
use App\Services\LanguageService;
use App\Services\SystemSettingService;
use App\Services\ThemeSettingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(
        Request $request,
        CatalogService $catalogService,
        BannerService $bannerService,
        LanguageService $languageService,
        CurrencyService $currencyService,
        SystemSettingService $settingService,
        ThemeSettingService $themeService,
    ): View {
        $defaultLanguage = $languageService->getDefault() ?? Language::query()->active()->firstOrFail();
        $language = $this->resolveLanguage($request->query('language', $request->session()->get('storefront_language')), $languageService, $defaultLanguage);
        $defaultCurrency = $currencyService->getDefault() ?? Currency::query()->active()->firstOrFail();
        $currency = $this->resolveCurrency($request->query('currency', $request->session()->get('storefront_currency')), $currencyService, $defaultCurrency);
        $request->session()->put(['storefront_language' => $language->code, 'storefront_currency' => $currency->code]);
        App::setLocale(in_array($language->code, ['vi', 'en', 'ja'], true) ? $language->code : config('app.fallback_locale'));

        $theme = $themeService->all();
        $sections = [
            'featured_categories' => (bool) $theme['show_featured_categories'],
            'featured_products' => (bool) $theme['show_featured_products'],
            'new_arrivals' => (bool) $theme['show_new_arrivals'],
            'best_sellers' => (bool) $theme['show_best_sellers'],
            'promotion_banner' => (bool) $theme['show_promotion_banner'],
            'newsletter' => (bool) $theme['show_newsletter'],
        ];

        return view('storefront.home', [
            'theme' => $theme,
            'themeService' => $themeService,
            'sections' => $sections,
            'categories' => $catalogService->categories()->take(8),
            'featuredProducts' => $this->productQuery()->where('is_featured', true)->latest()->take(8)->get(),
            'newArrivals' => $this->productQuery()->latest()->take(8)->get(),
            'bestSellers' => $this->bestSellers(),
            'homeBanners' => $sections['promotion_banner'] ? $bannerService->forPosition('home_top', $language, $defaultLanguage) : collect(),
            'bannerService' => $bannerService,
            'catalogService' => $catalogService,
            'filters' => [],
            'currentLanguage' => $language,
            'defaultLanguage' => $defaultLanguage,
            'languages' => collect($languageService->active()),
            'currentCurrency' => $currency,
            'baseCurrency' => $defaultCurrency,
            'currencies' => collect($currencyService->active()),
            'siteName' => $settingService->get('site_name', config('app.name')),
        ]);
    }

    private function productQuery(): Builder
    {
        return Product::query()
            ->active()
            ->whereHas('category', fn (Builder $query) => $query->active())
            ->whereHas('productTranslations')
            ->with([
                'productTranslations',
                'category.categoryTranslations',
                'productImages' => fn ($query) => $query->active()->orderByDesc('is_main')->orderBy('sort_order')->orderBy('id'),
                'productVariants' => fn ($query) => $query->where('status', true)->with(['variantImages' => fn ($query) => $query->active()]),
                'inventoryStocks',
            ]);
    }

    private function bestSellers()
    {
        $ids = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.order_status', '!=', 'cancelled')
            ->whereNotNull('order_items.product_id')
            ->select('order_items.product_id', DB::raw('SUM(order_items.quantity) as sold_quantity'))
            ->groupBy('order_items.product_id')
            ->orderByDesc('sold_quantity')
            ->limit(8)
            ->pluck('product_id')
            ->all();

        if ($ids === []) {
            return $this->productQuery()->where('is_featured', true)->latest()->take(8)->get();
        }

        $orderCase = collect($ids)
            ->values()
            ->map(fn (int|string $id, int $index): string => 'WHEN '.(int) $id.' THEN '.$index)
            ->implode(' ');

        return $this->productQuery()
            ->whereIn('id', $ids)
            ->orderByRaw("CASE id {$orderCase} ELSE 999 END")
            ->get();
    }

    private function resolveLanguage(?string $code, LanguageService $service, Language $fallback): Language
    {
        $language = $code ? $service->findByCode($code) : null;

        return $language?->status ? $language : $fallback;
    }

    private function resolveCurrency(?string $code, CurrencyService $service, Currency $fallback): Currency
    {
        $currency = $code ? $service->findByCode($code) : null;

        return $currency?->status ? $currency : $fallback;
    }
}
