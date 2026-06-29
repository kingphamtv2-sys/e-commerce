<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class StorefrontPageDataService
{
    public function __construct(
        private readonly LanguageService $languages,
        private readonly CurrencyService $currencies,
        private readonly SystemSettingService $settings,
    ) {}

    public function data(Request $request, array $extra = []): array
    {
        $defaultLanguage = $this->languages->getDefault() ?? Language::query()->active()->firstOrFail();
        $languageCode = $request->query('language', $request->session()->get('storefront_language'));
        $language = $languageCode
            ? $this->languages->findByCode((string) $languageCode)
            : null;
        $language = $language?->status ? $language : $defaultLanguage;
        $request->session()->put('storefront_language', $language->code);
        App::setLocale(in_array($language->code, ['vi', 'en', 'ja'], true) ? $language->code : config('app.fallback_locale'));

        $baseCurrency = $this->currencies->getDefault() ?? Currency::query()->active()->firstOrFail();
        $currencyCode = $request->query('currency', $request->session()->get('storefront_currency'));
        $currency = $currencyCode
            ? $this->currencies->findByCode((string) $currencyCode)
            : null;
        $currency = $currency?->status ? $currency : $baseCurrency;
        $request->session()->put('storefront_currency', $currency->code);

        return array_merge([
            'filters' => [],
            'currentLanguage' => $language,
            'defaultLanguage' => $defaultLanguage,
            'languages' => collect($this->languages->active()),
            'currentCurrency' => $currency,
            'baseCurrency' => $baseCurrency,
            'currencies' => collect($this->currencies->active()),
            'siteName' => $this->settings->get('site_name', config('app.name')),
        ], $extra);
    }
}
