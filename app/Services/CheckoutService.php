<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CheckoutSession;
use App\Models\Currency;
use App\Models\ShippingMethod;
use App\Models\TaxClass;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CouponService $couponService,
        private readonly TaxService $taxService,
        private readonly CurrencyService $currencyService,
        private readonly ShippingCalculationService $shippingCalculationService,
    ) {}

    public function summary(Request $request): array
    {
        [$cart, $items, $currency, $baseCurrency] = $this->validatedCart($request);
        $subtotal = round((float) $items->sum('subtotal'), 2);
        $coupon = $this->couponService->revalidateCart($cart, $request->user(), $items);
        $discount = round((float) $coupon['discount_amount'], 2);
        $shippingAddress = $this->shippingAddressFromRequest($request);
        $shippingSummary = $this->shippingSummary($request, $shippingAddress, max(0, $subtotal - $discount), $currency, $baseCurrency);
        $shipping = (float) ($shippingSummary['selected_shipping_method']['shipping_amount'] ?? 0);
        $tax = $this->taxSnapshot($items, $discount, $this->taxAddress($request));
        $taxAmount = round((float) collect($tax)->sum('tax_amount'), 2);
        $grandTotal = round((float) collect($tax)->sum('total_amount') + $shipping, 2);

        return [
            'cart' => $cart,
            'items' => $items,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shipping,
            'base_shipping_amount' => (float) ($shippingSummary['selected_shipping_method']['base_shipping_amount'] ?? 0),
            'grand_total' => $grandTotal,
            'available_shipping_methods' => $shippingSummary['available_shipping_methods'],
            'selected_shipping_method' => $shippingSummary['selected_shipping_method'],
            'shipping_required' => true,
            'has_available_shipping_methods' => $shippingSummary['available_shipping_methods']->isNotEmpty(),
            'tax_snapshot' => $tax,
            'currency_snapshot' => $this->currencySnapshot($currency),
            'coupon_snapshot' => $this->couponSnapshot($coupon),
            'formatted' => [
                'subtotal' => $this->format($subtotal, $currency, $baseCurrency),
                'discount_amount' => $this->format($discount, $currency, $baseCurrency),
                'tax_amount' => $this->format($taxAmount, $currency, $baseCurrency),
                'shipping_amount' => $this->format($shipping, $currency, $baseCurrency),
                'grand_total' => $this->format($grandTotal, $currency, $baseCurrency),
            ],
        ];
    }

    public function summaryPayload(Request $request): array
    {
        return $this->presentSummary($this->summary($request));
    }

    public function createSession(Request $request, array $data): CheckoutSession
    {
        return DB::transaction(function () use ($request, $data): CheckoutSession {
            $summary = $this->summary($request);
            $cart = $summary['cart'];
            if (! $summary['has_available_shipping_methods']) {
                throw new DomainException(__('storefront.shipping_no_methods'));
            }
            if (! $summary['selected_shipping_method']) {
                throw new DomainException(__('storefront.shipping_method_required'));
            }

            $shipping = $this->normalizeAddress($data['shipping']);
            $billing = ! empty($data['billing_same_as_shipping'])
                ? $shipping
                : $this->normalizeAddress($data['billing']);

            CheckoutSession::query()
                ->where('cart_id', $cart->id)
                ->where('status', 'draft')
                ->update(['status' => 'expired']);

            return CheckoutSession::query()->create([
                'cart_id' => $cart->id,
                'user_id' => $request->user()?->id,
                'session_id' => $cart->session_id ?: $request->session()->getId(),
                'token' => Str::random(80),
                'status' => 'draft',
                'contact_name' => $data['contact']['name'],
                'contact_email' => $data['contact']['email'],
                'contact_phone' => $data['contact']['phone'],
                'shipping_address' => $shipping,
                'billing_address' => $billing,
                'billing_same_as_shipping' => (bool) ($data['billing_same_as_shipping'] ?? false),
                'items_snapshot' => $this->itemsSnapshot($summary['items']),
                'tax_snapshot' => $summary['tax_snapshot'],
                'currency_snapshot' => $summary['currency_snapshot'],
                'coupon_snapshot' => $summary['coupon_snapshot'],
                ...$this->shippingCalculationService->snapshot($summary['selected_shipping_method']),
                'note' => $data['note'] ?? null,
                'subtotal' => $summary['subtotal'],
                'discount_amount' => $summary['discount_amount'],
                'tax_amount' => $summary['tax_amount'],
                'shipping_amount' => $summary['shipping_amount'],
                'grand_total' => $summary['grand_total'],
                'expires_at' => now()->addMinutes(30),
            ]);
        });
    }

    /** @return array{0: Cart, 1: Collection<int, array<string, mixed>>, 2: Currency, 3: Currency} */
    private function validatedCart(Request $request): array
    {
        [$currency, $baseCurrency] = $this->cartService->currencies($request);
        $cart = $this->cartService->currentCart($request);

        if (! $cart || $cart->cartItems->isEmpty()) {
            throw new DomainException(__('storefront.checkout_cart_empty'));
        }

        $items = $this->cartService->items($cart, $currency, $baseCurrency);
        if ($items->isEmpty()) {
            throw new DomainException(__('storefront.checkout_cart_empty'));
        }

        $invalid = $items->first(fn (array $item): bool => ! $item['available']);
        if ($invalid) {
            throw new DomainException($invalid['availability_message'] ?: __('storefront.checkout_cart_invalid'));
        }

        return [$cart, $items, $currency, $baseCurrency];
    }

    /** @return array<int, array<string, mixed>> */
    private function taxSnapshot(Collection $items, float $discount, array $address): array
    {
        $subtotal = max(0, (float) $items->sum('subtotal'));
        $remainingDiscount = $discount;
        $lastIndex = $items->count() - 1;

        return $items->values()->map(function (array $item, int $index) use ($subtotal, $discount, &$remainingDiscount, $lastIndex, $address): array {
            $itemSubtotal = (float) $item['subtotal'];
            $allocatedDiscount = $index === $lastIndex
                ? $remainingDiscount
                : round($subtotal > 0 ? $discount * ($itemSubtotal / $subtotal) : 0, 2);
            $remainingDiscount = round($remainingDiscount - $allocatedDiscount, 2);
            $taxableAmount = max(0, $itemSubtotal - $allocatedDiscount);
            $taxClass = $item['product']->taxClass;
            $calculation = $taxClass
                ? $this->taxService->calculate($taxableAmount, $taxClass, $address['country_code'], $address['region'])
                : ['rate' => 0.0, 'base_amount' => $taxableAmount, 'tax_amount' => 0.0, 'total_amount' => $taxableAmount];

            return [
                'cart_item_id' => $item['id'],
                'product_id' => $item['product']->id,
                'product_variant_id' => $item['variant']?->id,
                'tax_class_id' => $taxClass?->id,
                'tax_name' => $this->taxName($taxClass),
                'tax_rate' => (float) $calculation['rate'],
                'taxable_amount' => round($taxableAmount, 2),
                'tax_amount' => (float) $calculation['tax_amount'],
                'total_amount' => (float) $calculation['total_amount'],
            ];
        })->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function itemsSnapshot(Collection $items): array
    {
        return $items->map(fn (array $item): array => [
            'cart_item_id' => $item['id'],
            'product_id' => $item['product']->id,
            'product_variant_id' => $item['variant']?->id,
            'product_name' => $item['name'],
            'variant_name' => $item['variant_label'],
            'sku' => $item['sku'],
            'quantity' => $item['quantity'],
            'price' => round((float) $item['unit_price'], 2),
            'subtotal' => round((float) $item['subtotal'], 2),
            'image' => $item['image_url'],
        ])->values()->all();
    }

    private function currencySnapshot(Currency $currency): array
    {
        return [
            'code' => $currency->code,
            'rate' => (float) $currency->exchange_rate,
            'symbol' => $currency->symbol,
            'symbol_position' => $currency->symbol_position,
            'decimal_places' => $currency->decimal_places,
        ];
    }

    private function presentSummary(array $summary): array
    {
        return [
            'subtotal' => $summary['subtotal'],
            'discount_amount' => $summary['discount_amount'],
            'tax_amount' => $summary['tax_amount'],
            'shipping_amount' => $summary['shipping_amount'],
            'base_shipping_amount' => $summary['base_shipping_amount'],
            'grand_total' => $summary['grand_total'],
            'formatted' => $summary['formatted'],
            'available_shipping_methods' => $summary['available_shipping_methods']->all(),
            'selected_shipping_method' => $summary['selected_shipping_method'],
            'shipping_required' => $summary['shipping_required'],
            'has_available_shipping_methods' => $summary['has_available_shipping_methods'],
            'tax_snapshot' => $summary['tax_snapshot'],
            'currency_snapshot' => $summary['currency_snapshot'],
            'coupon_snapshot' => $summary['coupon_snapshot'],
        ];
    }

    public function shippingMethodsPayload(Request $request): array
    {
        $summary = $this->summary($request);

        return [
            'available_shipping_methods' => $summary['available_shipping_methods']->all(),
            'selected_shipping_method' => $summary['selected_shipping_method'],
            'shipping_required' => $summary['shipping_required'],
            'has_available_shipping_methods' => $summary['has_available_shipping_methods'],
        ];
    }

    public function selectShippingMethod(Request $request, ShippingMethod $method): array
    {
        [$cart, $items, $currency, $baseCurrency] = $this->validatedCart($request);
        $subtotal = round((float) $items->sum('subtotal'), 2);
        $coupon = $this->couponService->revalidateCart($cart, $request->user(), $items);
        $discount = round((float) $coupon['discount_amount'], 2);
        $summary = $this->shippingCalculationService->calculate(
            $method,
            $this->shippingAddressFromRequest($request),
            max(0, $subtotal - $discount),
            $currency,
            $baseCurrency,
        );

        $request->session()->put('checkout_shipping_method_id', $method->id);

        return $summary;
    }

    private function couponSnapshot(array $coupon): ?array
    {
        $model = $coupon['coupon'] ?? null;

        if (! $model) {
            return null;
        }

        return [
            'id' => $model->id,
            'code' => $model->code,
            'discount_amount' => round((float) $coupon['discount_amount'], 2),
        ];
    }

    private function normalizeAddress(array $address): array
    {
        return [
            'full_name' => $address['full_name'],
            'phone' => $address['phone'],
            'country_code' => strtoupper($address['country_code'] ?? 'VN'),
            'province' => $address['province'] ?? null,
            'district' => $address['district'] ?? null,
            'ward' => $address['ward'] ?? null,
            'address_line' => $address['address_line'],
        ];
    }

    private function shippingSummary(Request $request, array $address, float $eligibleSubtotal, Currency $currency, Currency $baseCurrency): array
    {
        $methods = $this->shippingCalculationService->availableMethods($address, $eligibleSubtotal, $currency, $baseCurrency);
        $selectedId = $request->integer('shipping_method_id') ?: (int) $request->session()->get('checkout_shipping_method_id');
        $selected = null;

        if ($selectedId) {
            $selected = $methods->firstWhere('id', $selectedId);
            if ($selected) {
                $request->session()->put('checkout_shipping_method_id', $selectedId);
            } else {
                $request->session()->forget('checkout_shipping_method_id');
            }
        }

        return [
            'available_shipping_methods' => $methods,
            'selected_shipping_method' => $selected,
        ];
    }

    private function shippingAddressFromRequest(Request $request): array
    {
        return [
            'country_code' => strtoupper((string) $request->input('shipping.country_code', 'VN')),
            'province' => (string) $request->input('shipping.province', ''),
            'district' => (string) $request->input('shipping.district', ''),
        ];
    }

    private function taxAddress(Request $request): array
    {
        return [
            'country_code' => strtoupper((string) $request->input('shipping.country_code', 'VN')),
            'region' => $request->input('shipping.province'),
        ];
    }

    private function taxName(?TaxClass $taxClass): string
    {
        return $taxClass?->name ?? __('storefront.no_tax');
    }

    private function format(float $amount, Currency $currency, Currency $baseCurrency): string
    {
        $converted = $this->currencyService->convert($amount, $baseCurrency, $currency);

        return $this->currencyService->format($converted, $currency);
    }
}
