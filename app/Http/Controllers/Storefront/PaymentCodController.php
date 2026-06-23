<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CheckoutSession;
use App\Models\Currency;
use App\Models\Language;
use App\Services\CurrencyService;
use App\Services\LanguageService;
use App\Services\PaymentCodService;
use App\Services\SystemSettingService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class PaymentCodController extends Controller
{
    public function show(
        Request $request,
        string $token,
        PaymentCodService $paymentCodService,
        LanguageService $languageService,
        CurrencyService $currencyService,
        SystemSettingService $settingService,
    ): View|RedirectResponse {
        [$language, $defaultLanguage] = $this->languages($request, $languageService);
        [$currency, $baseCurrency] = $this->currencies($request, $currencyService);
        $completedSession = CheckoutSession::query()
            ->where('token', $token)
            ->where('status', 'completed')
            ->with('order')
            ->first();

        if ($completedSession?->order) {
            return redirect()->route('orders.success', $completedSession->order->success_token);
        }

        try {
            $data = $paymentCodService->paymentPageData($request, $token);
        } catch (DomainException $exception) {
            return redirect()->route('checkout.index')->withErrors(['payment' => $exception->getMessage()]);
        }

        return view('storefront.checkout.payment', [
            ...$data,
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

    public function store(Request $request, string $token, PaymentCodService $paymentCodService): JsonResponse|RedirectResponse
    {
        try {
            $session = $paymentCodService->select($request, $token);
        } catch (DomainException $exception) {
            return $request->expectsJson()
                ? $this->error($exception->getMessage())
                : back()->withErrors(['payment' => $exception->getMessage()]);
        }

        if (! $request->expectsJson()) {
            return back()->with('success', __('storefront.payment_cod_selected'));
        }

        return response()->json([
            'success' => true,
            'message' => __('storefront.payment_cod_selected'),
            'payment' => [
                'payment_method_code' => $session->payment_method_code,
                'payment_method_name' => $session->payment_method_name,
                'payment_status' => $session->payment_status,
                'payment_amount' => (float) $session->payment_amount,
                'payment_currency_code' => $session->payment_currency_code,
                'payment_instruction' => $session->payment_instruction,
                'payment_selected_at' => $session->payment_selected_at?->toIso8601String(),
            ],
            'ready_to_order' => true,
        ]);
    }

    private function error(string $message): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => ['payment' => [$message]]], 422);
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
