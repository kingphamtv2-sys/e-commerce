<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductDetailRequest;
use App\Models\Currency;
use App\Models\Language;
use App\Services\CatalogService;
use App\Services\CurrencyService;
use App\Services\LanguageService;
use App\Services\ProductDetailService;
use App\Services\SystemSettingService;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class ProductDetailController extends Controller
{
    public function __invoke(
        ProductDetailRequest $request,
        string $slug,
        ProductDetailService $detailService,
        CatalogService $catalogService,
        LanguageService $languageService,
        CurrencyService $currencyService,
        SystemSettingService $settingService,
    ): View {
        $query = $request->validated();
        $defaultLanguage = $languageService->getDefault() ?? Language::query()->active()->firstOrFail();
        $language = $this->resolveLanguage($query['language'] ?? $request->session()->get('storefront_language'), $languageService, $defaultLanguage);
        $baseCurrency = $currencyService->getDefault() ?? Currency::query()->active()->firstOrFail();
        $currency = $this->resolveCurrency($query['currency'] ?? $request->session()->get('storefront_currency'), $currencyService, $baseCurrency);
        $product = $detailService->findVisibleBySlug($slug, $language, $defaultLanguage);
        $translation = $detailService->translation($product, $language);

        abort_if($translation === null, 404);

        $request->session()->put(['storefront_language' => $language->code, 'storefront_currency' => $currency->code]);
        App::setLocale(in_array($language->code, ['vi', 'en', 'ja'], true) ? $language->code : config('app.fallback_locale'));

        return view('storefront.products.show', [
            'product' => $product,
            'translation' => $translation,
            'relatedProducts' => $detailService->relatedProducts($product),
            'variantOptions' => $detailService->variantOptions($product, $currency, $baseCurrency),
            'productOptions' => $product->productOptions,
            'availableQuantity' => $detailService->availableQuantity($product),
            'catalogService' => $catalogService,
            'detailService' => $detailService,
            'filters' => [],
            'currentLanguage' => $language,
            'defaultLanguage' => $defaultLanguage,
            'languages' => collect($languageService->active()),
            'currentCurrency' => $currency,
            'baseCurrency' => $baseCurrency,
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
