<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Language;
use App\Services\CurrencyService;
use App\Services\LanguageService;
use App\Services\OrderCreationService;
use App\Services\SystemSettingService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class OrderCreationController extends Controller
{
    public function store(Request $request, string $token, OrderCreationService $orderCreationService): JsonResponse|RedirectResponse
    {
        try {
            $order = $orderCreationService->createFromCheckoutSession($request, $token);
        } catch (DomainException $exception) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $exception->getMessage(), 'errors' => ['order' => [$exception->getMessage()]]], 422)
                : back()->withErrors(['order' => $exception->getMessage()]);
        }

        $url = route('orders.success', $order->success_token);

        if (! $request->expectsJson()) {
            return redirect($url);
        }

        return response()->json([
            'success' => true,
            'message' => __('storefront.order_created'),
            'redirect_url' => $url,
            'order' => [
                'order_code' => $order->order_code,
                'success_token' => $order->success_token,
            ],
        ]);
    }

    public function success(
        Request $request,
        string $token,
        OrderCreationService $orderCreationService,
        LanguageService $languageService,
        CurrencyService $currencyService,
        SystemSettingService $settingService,
    ): View {
        [$language, $defaultLanguage] = $this->languages($request, $languageService);
        [$currency, $baseCurrency] = $this->currencies($request, $currencyService);
        $order = $orderCreationService->successOrder($request, $token);
        abort_if(! $order, 404);

        return view('storefront.orders.success', [
            'order' => $order,
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

    private function languages(Request $request, LanguageService $service): array
    {
        $default = $service->getDefault() ?? Language::query()->active()->firstOrFail();
        $language = $request->session()->get('storefront_language') ? $service->findByCode($request->session()->get('storefront_language')) : null;
        $language = $language?->status ? $language : $default;
        $request->session()->put('storefront_language', $language->code);
        App::setLocale(in_array($language->code, ['vi', 'en', 'ja'], true) ? $language->code : config('app.fallback_locale'));

        return [$language, $default];
    }

    private function currencies(Request $request, CurrencyService $service): array
    {
        $default = $service->getDefault() ?? Currency::query()->active()->firstOrFail();
        $currency = $request->session()->get('storefront_currency') ? $service->findByCode($request->session()->get('storefront_currency')) : null;
        $currency = $currency?->status ? $currency : $default;
        $request->session()->put('storefront_currency', $currency->code);

        return [$currency, $default];
    }
}
