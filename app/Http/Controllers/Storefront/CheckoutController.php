<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Currency;
use App\Models\Language;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\CurrencyService;
use App\Services\LanguageService;
use App\Services\SystemSettingService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(
        Request $request,
        CheckoutService $checkoutService,
        CartService $cartService,
        LanguageService $languageService,
        CurrencyService $currencyService,
        SystemSettingService $settingService,
    ): View|RedirectResponse {
        [$language, $defaultLanguage] = $this->languages($request, $languageService);
        [$currency, $baseCurrency] = $this->currencies($request, $currencyService);

        try {
            $summary = $checkoutService->summary($request);
        } catch (DomainException $exception) {
            return redirect()->route('cart.index')->withErrors(['checkout' => $exception->getMessage()]);
        }

        return view('storefront.checkout.index', [
            'cart' => $summary['cart'],
            'cartItems' => $cartService->items($summary['cart'], $currency, $baseCurrency),
            'checkoutSummary' => $summary,
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

    public function summary(Request $request, CheckoutService $checkoutService): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'summary' => $checkoutService->summaryPayload($request)]);
        } catch (DomainException $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function store(CheckoutRequest $request, CheckoutService $checkoutService): JsonResponse|RedirectResponse
    {
        try {
            $session = $checkoutService->createSession($request, $request->validated());
            $summary = $checkoutService->summaryPayload($request);
        } catch (DomainException $exception) {
            return $request->expectsJson()
                ? $this->error($exception->getMessage())
                : back()->withErrors(['checkout' => $exception->getMessage()])->withInput();
        }

        if (! $request->expectsJson()) {
            return back()->with('success', __('storefront.checkout_session_created'))->with('checkout_token', $session->token);
        }

        return response()->json([
            'success' => true,
            'message' => __('storefront.checkout_session_created'),
            'checkout_session' => [
                'token' => $session->token,
                'expires_at' => $session->expires_at?->toIso8601String(),
            ],
            'summary' => $summary,
        ]);
    }

    private function error(string $message): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => ['checkout' => [$message]]], 422);
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
