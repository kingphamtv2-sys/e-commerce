<?php

namespace App\Services;

use App\Models\CheckoutSession;
use App\Models\Currency;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentCodService
{
    public const METHOD_CODE = 'cod';

    public const INITIAL_STATUS = 'pending';

    public function __construct(
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService,
        private readonly CurrencyService $currencyService,
        private readonly SystemSettingService $settings,
    ) {}

    public function settings(): array
    {
        return [
            'enabled' => (bool) $this->settings->get('payment_cod_enabled', true),
            'display_name' => (string) $this->settings->get('payment_cod_display_name', 'Cash on Delivery'),
            'description' => $this->nullableString($this->settings->get('payment_cod_description')),
            'instruction' => $this->nullableString($this->settings->get('payment_cod_instruction')),
            'min_order_amount' => $this->nullableFloat($this->settings->get('payment_cod_min_order_amount')),
            'max_order_amount' => $this->nullableFloat($this->settings->get('payment_cod_max_order_amount')),
            'sort_order' => (int) $this->settings->get('payment_cod_sort_order', 10),
        ];
    }

    public function paymentPageData(Request $request, string $token): array
    {
        $session = $this->validatedCheckoutSession($request, $token);
        $cod = $this->settings();
        $available = true;
        $message = null;

        try {
            $this->assertCodSelectable($request, $session, $cod);
        } catch (DomainException $exception) {
            $available = false;
            $message = $exception->getMessage();
        }

        return [
            'checkoutSession' => $session,
            'cod' => $cod,
            'codAvailable' => $available,
            'codUnavailableMessage' => $message,
            'formattedPaymentAmount' => $this->formatSessionAmount($session),
        ];
    }

    public function select(Request $request, string $token): CheckoutSession
    {
        return DB::transaction(function () use ($request, $token): CheckoutSession {
            $session = $this->validatedCheckoutSession($request, $token, true);
            $cod = $this->settings();
            $this->assertCodSelectable($request, $session, $cod);

            $session->forceFill([
                'payment_method_code' => self::METHOD_CODE,
                'payment_method_name' => $cod['display_name'],
                'payment_status' => self::INITIAL_STATUS,
                'payment_amount' => (float) $session->grand_total,
                'payment_currency_code' => $session->currency_snapshot['code'] ?? $this->settings->get('default_currency', 'VND'),
                'payment_instruction' => $cod['instruction'],
                'payment_selected_at' => now(),
            ])->save();

            return $session->refresh();
        });
    }

    public function validatedCheckoutSession(Request $request, string $token, bool $lock = false): CheckoutSession
    {
        $query = CheckoutSession::query()->where('token', $token)->with('cart.cartItems');
        if ($lock) {
            $query->lockForUpdate();
        }

        $session = $query->first();
        if (! $session) {
            throw new DomainException(__('storefront.payment_session_invalid'));
        }

        if ($session->status !== 'draft') {
            throw new DomainException(__('storefront.payment_session_invalid'));
        }

        if ($session->expires_at && $session->expires_at->isPast()) {
            $session->forceFill(['status' => 'expired'])->save();
            throw new DomainException(__('storefront.payment_session_expired'));
        }

        $cart = $this->cartService->currentCart($request);
        if (! $cart || $cart->id !== $session->cart_id) {
            throw new DomainException(__('storefront.payment_session_forbidden'));
        }

        $this->assertCheckoutSessionOwnership($request, $session);

        return $session;
    }

    public function assertCheckoutSessionOwnership(Request $request, CheckoutSession $session): void
    {
        if ($session->user_id) {
            if ($session->user_id !== $request->user()?->id) {
                throw new DomainException(__('storefront.payment_session_forbidden'));
            }

            return;
        }

        if (! hash_equals((string) $session->session_id, (string) $request->session()->get('cart_session_id'))) {
            throw new DomainException(__('storefront.payment_session_forbidden'));
        }
    }

    private function assertCodSelectable(Request $request, CheckoutSession $session, array $cod): void
    {
        if (! $cod['enabled']) {
            throw new DomainException(__('storefront.payment_cod_disabled'));
        }

        $summary = $this->checkoutSummaryForSession($request, $session);
        if ($summary['cart']->id !== $session->cart_id) {
            throw new DomainException(__('storefront.payment_session_forbidden'));
        }

        $this->assertCheckoutSnapshotStillMatches($session, $summary);

        $grandTotal = (float) $session->grand_total;
        if ($cod['min_order_amount'] !== null && $grandTotal < $cod['min_order_amount']) {
            throw new DomainException(__('storefront.payment_cod_min_order', [
                'amount' => $this->formatBaseAmount($cod['min_order_amount']),
            ]));
        }

        if ($cod['max_order_amount'] !== null && $grandTotal > $cod['max_order_amount']) {
            throw new DomainException(__('storefront.payment_cod_max_order', [
                'amount' => $this->formatBaseAmount($cod['max_order_amount']),
            ]));
        }
    }

    public function assertCheckoutSnapshotStillMatches(CheckoutSession $session, array $summary): void
    {
        $amounts = ['subtotal', 'discount_amount', 'tax_amount', 'shipping_amount', 'grand_total'];
        foreach ($amounts as $amount) {
            if (round((float) $session->{$amount}, 2) !== round((float) $summary[$amount], 2)) {
                throw new DomainException(__('storefront.payment_session_changed'));
            }
        }

        $summaryItems = collect($summary['items'])
            ->mapWithKeys(fn (array $item): array => [
                $item['id'] => [
                    'quantity' => (int) $item['quantity'],
                    'subtotal' => round((float) $item['subtotal'], 2),
                ],
            ]);

        $snapshotItems = collect($session->items_snapshot ?? []);
        if ($snapshotItems->count() !== $summaryItems->count()) {
            throw new DomainException(__('storefront.payment_session_changed'));
        }

        foreach ($snapshotItems as $item) {
            $current = $summaryItems->get($item['cart_item_id'] ?? null);
            if (! $current || $current['quantity'] !== (int) ($item['quantity'] ?? 0) || $current['subtotal'] !== round((float) ($item['subtotal'] ?? 0), 2)) {
                throw new DomainException(__('storefront.payment_session_changed'));
            }
        }
    }

    public function checkoutSummaryForSession(Request $request, CheckoutSession $session): array
    {
        $shipping = $session->shipping_address ?? [];
        $request->merge([
            'shipping_method_id' => $session->shipping_method_id,
            'shipping' => [
                'country_code' => $shipping['country_code'] ?? 'VN',
                'province' => $shipping['province'] ?? null,
                'district' => $shipping['district'] ?? null,
            ],
        ]);

        return $this->checkoutService->summary($request);
    }

    private function formatSessionAmount(CheckoutSession $session): string
    {
        $baseCurrency = $this->currencyService->getDefault() ?? Currency::query()->active()->firstOrFail();
        $currency = Currency::query()->where('code', $session->currency_snapshot['code'] ?? $baseCurrency->code)->first() ?: $baseCurrency;

        return $this->currencyService->format(
            $this->currencyService->convert((float) $session->grand_total, $baseCurrency, $currency),
            $currency,
        );
    }

    private function formatBaseAmount(float $amount): string
    {
        $baseCurrency = $this->currencyService->getDefault() ?? Currency::query()->active()->firstOrFail();

        return $this->currencyService->format($amount, $baseCurrency);
    }

    private function nullableFloat(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
