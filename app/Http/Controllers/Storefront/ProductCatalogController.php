<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogRequest;
use App\Models\Currency;
use App\Models\Language;
use App\Services\CatalogService;
use App\Services\CurrencyService;
use App\Services\LanguageService;
use App\Services\SystemSettingService;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    public function __invoke(
        CatalogRequest $request,
        CatalogService $catalogService,
        LanguageService $languageService,
        CurrencyService $currencyService,
        SystemSettingService $settingService,
    ): View {
        $filters = $request->validated();
        $defaultLanguage = $languageService->getDefault() ?? Language::query()->active()->firstOrFail();
        $language = $this->resolveLanguage($filters['language'] ?? $request->session()->get('storefront_language'), $languageService, $defaultLanguage);
        $defaultCurrency = $currencyService->getDefault() ?? Currency::query()->active()->firstOrFail();
        $currency = $this->resolveCurrency($filters['currency'] ?? $request->session()->get('storefront_currency'), $currencyService, $defaultCurrency);

        $request->session()->put(['storefront_language' => $language->code, 'storefront_currency' => $currency->code]);
        App::setLocale(in_array($language->code, ['vi', 'en', 'ja'], true) ? $language->code : config('app.fallback_locale'));
        $selectedCategory = $catalogService->resolveCategory($filters['category'] ?? null, $language, $defaultLanguage);

        return view('storefront.products.index', [
            'products' => $catalogService->products($filters, $language, $defaultLanguage),
            'categories' => $catalogService->categories(),
            'selectedCategory' => $selectedCategory,
            'catalogService' => $catalogService,
            'filters' => $filters,
            'currentLanguage' => $language,
            'defaultLanguage' => $defaultLanguage,
            'languages' => collect($languageService->active()),
            'currentCurrency' => $currency,
            'baseCurrency' => $defaultCurrency,
            'currencies' => collect($currencyService->active()),
            'siteName' => $settingService->get('site_name', config('app.name')),
        ]);
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
