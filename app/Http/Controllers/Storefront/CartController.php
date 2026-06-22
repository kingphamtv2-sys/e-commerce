<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Currency;
use App\Models\Language;
use App\Services\CartService;
use App\Services\CurrencyService;
use App\Services\LanguageService;
use App\Services\SystemSettingService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(
        Request $request,
        CartService $cartService,
        LanguageService $languageService,
        CurrencyService $currencyService,
        SystemSettingService $settingService,
    ): View {
        [$language, $defaultLanguage] = $this->languages($request, $languageService);
        [$currency, $baseCurrency] = $this->currencies($request, $currencyService);
        $cart = $cartService->currentCart($request);
        $items = $cart ? $cartService->items($cart, $currency, $baseCurrency) : collect();

        return view('storefront.cart.index', [
            'cart' => $cart,
            'cartItems' => $items,
            'cartSummary' => $cartService->summary($cart, $currency, $baseCurrency),
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

    public function store(AddToCartRequest $request, CartService $cartService): JsonResponse|RedirectResponse
    {
        try {
            $item = $cartService->add($request, (int) $request->validated('product_id'), $request->integer('product_variant_id') ?: null, (int) $request->validated('quantity'));
        } catch (DomainException $exception) {
            return $this->error($request, $exception->getMessage());
        }

        if (! $request->expectsJson()) {
            return redirect()->route('cart.index')->with('success', __('storefront.cart_added'));
        }

        return response()->json([
            'success' => true,
            'message' => __('storefront.cart_added'),
            ...$cartService->responsePayload($request, $item),
        ]);
    }

    public function update(UpdateCartItemRequest $request, CartItem $item, CartService $cartService): JsonResponse|RedirectResponse
    {
        try {
            $item = $cartService->updateQuantity($request, $item, (int) $request->validated('quantity'));
        } catch (DomainException $exception) {
            return $this->error($request, $exception->getMessage());
        }

        if (! $request->expectsJson()) {
            return back()->with('success', __('storefront.cart_updated'));
        }

        return response()->json([
            'success' => true,
            'message' => __('storefront.cart_updated'),
            ...$cartService->responsePayload($request, $item),
        ]);
    }

    public function destroy(Request $request, CartItem $item, CartService $cartService): JsonResponse|RedirectResponse
    {
        try {
            $cartService->remove($request, $item);
        } catch (DomainException $exception) {
            return $this->error($request, $exception->getMessage());
        }

        if (! $request->expectsJson()) {
            return back()->with('success', __('storefront.cart_removed'));
        }

        $payload = $cartService->responsePayload($request);

        return response()->json([
            'success' => true,
            'message' => __('storefront.cart_removed'),
            'removed_item_id' => $item->id,
            'is_empty' => $payload['summary']['is_empty'],
            ...$payload,
        ]);
    }

    public function clear(Request $request, CartService $cartService): JsonResponse|RedirectResponse
    {
        $cartService->clear($request);

        if (! $request->expectsJson()) {
            return back()->with('success', __('storefront.cart_cleared'));
        }

        return response()->json([
            'success' => true,
            'message' => __('storefront.cart_cleared'),
            ...$cartService->responsePayload($request),
        ]);
    }

    public function summary(Request $request, CartService $cartService): JsonResponse
    {
        return response()->json(['success' => true, ...$cartService->responsePayload($request)]);
    }

    private function error(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message, 'errors' => ['cart' => [$message]]], 422);
        }

        return back()->withErrors(['cart' => $message]);
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
