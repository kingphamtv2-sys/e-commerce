<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CheckoutSession;
use App\Models\Coupon;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\ProductVariant;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderCreationService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService,
        private readonly SystemSettingService $settings,
    ) {}

    public function createFromCheckoutSession(Request $request, string $token): Order
    {
        return DB::transaction(function () use ($request, $token): Order {
            $session = CheckoutSession::query()
                ->where('token', $token)
                ->with(['order', 'cart.cartItems.product.category', 'cart.cartItems.productVariant.optionValues.option'])
                ->lockForUpdate()
                ->first();

            if (! $session) {
                throw new DomainException(__('storefront.order_checkout_invalid'));
            }

            $this->assertCheckoutSessionOwnership($request, $session);

            if ($session->status === 'completed' && $session->order) {
                return $session->order;
            }

            $this->assertCheckoutSessionUsable($request, $session);
            $summary = $this->checkoutSummaryForSession($request, $session);
            $this->assertCheckoutSnapshotStillMatches($session, $summary);
            $lockedStocks = $this->lockAndValidateStocks($summary['cart'], $summary['items']);

            $order = Order::query()->create([
                'user_id' => $session->user_id,
                'checkout_session_id' => $session->id,
                'order_code' => $this->generateOrderCode(),
                'success_token' => Str::random(80),
                'customer_name' => $session->contact_name,
                'customer_phone' => $session->contact_phone,
                'customer_email' => $session->contact_email,
                'contact_snapshot' => [
                    'name' => $session->contact_name,
                    'email' => $session->contact_email,
                    'phone' => $session->contact_phone,
                ],
                'shipping_address' => $this->addressLine($session->shipping_address ?? []),
                'shipping_address_snapshot' => $session->shipping_address,
                'billing_address_snapshot' => $session->billing_address,
                'currency_code' => $session->currency_snapshot['code'] ?? $session->payment_currency_code,
                'currency_symbol' => $session->currency_snapshot['symbol'] ?? null,
                'currency_symbol_position' => $session->currency_snapshot['symbol_position'] ?? null,
                'currency_decimal_places' => $session->currency_snapshot['decimal_places'] ?? null,
                'currency_snapshot' => $session->currency_snapshot,
                'exchange_rate' => $session->currency_snapshot['rate'] ?? 1,
                'subtotal' => $session->subtotal,
                'discount_amount' => $session->discount_amount,
                'coupon_snapshot' => $session->coupon_snapshot,
                'tax_amount' => $session->tax_amount,
                'tax_snapshot' => $session->tax_snapshot,
                'shipping_fee' => $session->shipping_amount,
                'total_amount' => $session->grand_total,
                'payment_method' => $session->payment_method_code,
                'payment_method_name' => $session->payment_method_name,
                'payment_status' => $session->payment_status ?: 'pending',
                'payment_instruction' => $session->payment_instruction,
                'order_status' => 'pending',
                'note' => $session->note,
                'placed_at' => now(),
            ]);

            $this->createAddresses($order, $session);
            $this->createItems($order, $summary['items'], $session);
            $this->createPaymentSnapshots($order, $session);
            $this->deductInventory($order, $summary['items'], $lockedStocks);
            $this->recordCouponUsage($order, $session);

            $session->forceFill(['status' => 'completed'])->save();
            $summary['cart']->forceFill(['status' => 'converted'])->save();

            return $order->refresh()->load(['orderItems', 'orderAddresses', 'orderPayments']);
        });
    }

    public function successOrder(string $token): ?Order
    {
        return Order::query()
            ->where('success_token', $token)
            ->with(['orderItems', 'orderAddresses', 'orderPayments'])
            ->first();
    }

    private function assertCheckoutSessionUsable(Request $request, CheckoutSession $session): void
    {
        if ($session->status !== 'draft') {
            throw new DomainException(__('storefront.order_checkout_invalid'));
        }

        if ($session->expires_at && $session->expires_at->isPast()) {
            $session->forceFill(['status' => 'expired'])->save();
            throw new DomainException(__('storefront.order_checkout_expired'));
        }

        if (! $session->payment_method_code || ! $session->payment_method_name || ! $session->payment_selected_at) {
            throw new DomainException(__('storefront.order_payment_required'));
        }

        $cart = $this->cartService->currentCart($request);
        if (! $cart || $cart->id !== $session->cart_id) {
            throw new DomainException(__('storefront.order_checkout_forbidden'));
        }

        $this->assertCheckoutSessionOwnership($request, $session);
    }

    private function assertCheckoutSessionOwnership(Request $request, CheckoutSession $session): void
    {
        if ($session->user_id) {
            if ($session->user_id !== $request->user()?->id) {
                throw new DomainException(__('storefront.order_checkout_forbidden'));
            }

            return;
        }

        if (! hash_equals((string) $session->session_id, (string) $request->session()->get('cart_session_id'))) {
            throw new DomainException(__('storefront.order_checkout_forbidden'));
        }
    }

    private function checkoutSummaryForSession(Request $request, CheckoutSession $session): array
    {
        $shipping = $session->shipping_address ?? [];
        $request->merge([
            'shipping' => [
                'country_code' => $shipping['country_code'] ?? 'VN',
                'province' => $shipping['province'] ?? null,
            ],
        ]);

        return $this->checkoutService->summary($request);
    }

    private function assertCheckoutSnapshotStillMatches(CheckoutSession $session, array $summary): void
    {
        foreach (['subtotal', 'discount_amount', 'tax_amount', 'shipping_amount', 'grand_total'] as $amount) {
            if (round((float) $session->{$amount}, 2) !== round((float) $summary[$amount], 2)) {
                throw new DomainException(__('storefront.order_checkout_changed'));
            }
        }

        $summaryItems = collect($summary['items'])->keyBy('id');
        $snapshotItems = collect($session->items_snapshot ?? []);
        if ($snapshotItems->count() !== $summaryItems->count()) {
            throw new DomainException(__('storefront.order_checkout_changed'));
        }

        foreach ($snapshotItems as $item) {
            $current = $summaryItems->get($item['cart_item_id'] ?? null);
            if (! $current || (int) $current['quantity'] !== (int) ($item['quantity'] ?? 0) || round((float) $current['subtotal'], 2) !== round((float) ($item['subtotal'] ?? 0), 2)) {
                throw new DomainException(__('storefront.order_checkout_changed'));
            }
        }
    }

    /** @return array<int, InventoryStock> */
    private function lockAndValidateStocks(Cart $cart, iterable $items): array
    {
        if ($cart->cartItems->isEmpty()) {
            throw new DomainException(__('storefront.checkout_cart_empty'));
        }

        $locked = [];
        foreach ($items as $item) {
            if (! ($item['available'] ?? false)) {
                throw new DomainException($item['availability_message'] ?: __('storefront.checkout_cart_invalid'));
            }

            $stock = InventoryStock::query()
                ->where('product_id', $item['product']->id)
                ->when(
                    $item['variant'],
                    fn ($query) => $query->where('product_variant_id', $item['variant']->id),
                    fn ($query) => $query->whereNull('product_variant_id'),
                )
                ->lockForUpdate()
                ->first();

            if (! $stock || $stock->availableQuantity() < (int) $item['quantity']) {
                throw new DomainException(__('storefront.order_stock_unavailable'));
            }

            $locked[$item['id']] = $stock;
        }

        return $locked;
    }

    private function createAddresses(Order $order, CheckoutSession $session): void
    {
        foreach (['shipping' => $session->shipping_address ?? [], 'billing' => $session->billing_address ?? []] as $type => $address) {
            $order->orderAddresses()->create([
                'type' => $type,
                'full_name' => $address['full_name'] ?? $session->contact_name,
                'phone' => $address['phone'] ?? $session->contact_phone,
                'country_code' => $address['country_code'] ?? null,
                'province' => $address['province'] ?? null,
                'district' => $address['district'] ?? null,
                'ward' => $address['ward'] ?? null,
                'address_line' => $address['address_line'] ?? '',
                'raw_snapshot' => $address,
            ]);
        }
    }

    private function createItems(Order $order, iterable $items, CheckoutSession $session): void
    {
        $taxByCartItem = collect($session->tax_snapshot ?? [])->keyBy('cart_item_id');

        foreach ($items as $item) {
            $tax = $taxByCartItem->get($item['id'], []);
            $order->orderItems()->create([
                'product_id' => $item['product']->id,
                'product_variant_id' => $item['variant']?->id,
                'product_name' => $item['name'],
                'product_sku' => $item['sku'],
                'variant_name' => $item['variant_label'],
                'sku' => $item['sku'],
                'option_values_snapshot' => $this->optionValuesSnapshot($item['variant']),
                'image' => $item['image_url'],
                'price' => round((float) $item['unit_price'], 2),
                'quantity' => (int) $item['quantity'],
                'subtotal' => round((float) $item['subtotal'], 2),
                'tax_name' => $tax['tax_name'] ?? null,
                'taxable_amount' => round((float) ($tax['taxable_amount'] ?? 0), 2),
                'tax_rate' => (float) ($tax['tax_rate'] ?? 0),
                'tax_amount' => round((float) ($tax['tax_amount'] ?? 0), 2),
                'total' => round((float) ($tax['total_amount'] ?? $item['subtotal']), 2),
                'product_snapshot' => [
                    'product_id' => $item['product']->id,
                    'product_variant_id' => $item['variant']?->id,
                    'product_name' => $item['name'],
                    'variant_name' => $item['variant_label'],
                    'sku' => $item['sku'],
                    'image' => $item['image_url'],
                ],
            ]);
        }
    }

    private function createPaymentSnapshots(Order $order, CheckoutSession $session): void
    {
        $snapshot = [
            'payment_method_code' => $session->payment_method_code,
            'payment_method_name' => $session->payment_method_name,
            'payment_status' => $session->payment_status ?: 'pending',
            'payment_amount' => (float) $session->payment_amount,
            'payment_currency_code' => $session->payment_currency_code,
            'payment_instruction' => $session->payment_instruction,
            'payment_selected_at' => $session->payment_selected_at?->toIso8601String(),
        ];

        $order->orderPayments()->create([
            'payment_method_code' => $session->payment_method_code,
            'payment_method_name' => $session->payment_method_name,
            'payment_status' => $session->payment_status ?: 'pending',
            'amount' => $session->payment_amount ?: $session->grand_total,
            'currency_code' => $session->payment_currency_code ?: ($session->currency_snapshot['code'] ?? 'VND'),
            'instruction' => $session->payment_instruction,
            'selected_at' => $session->payment_selected_at,
            'snapshot' => $snapshot,
        ]);

        $order->payment()->create([
            'payment_method' => $session->payment_method_code,
            'amount' => $session->payment_amount ?: $session->grand_total,
            'currency_code' => $session->payment_currency_code ?: ($session->currency_snapshot['code'] ?? 'VND'),
            'status' => $session->payment_status ?: 'pending',
            'raw_response' => $snapshot,
        ]);
    }

    /** @param array<int, InventoryStock> $lockedStocks */
    private function deductInventory(Order $order, iterable $items, array $lockedStocks): void
    {
        foreach ($items as $item) {
            $stock = $lockedStocks[$item['id']];
            $before = $stock->quantity;
            $change = -1 * (int) $item['quantity'];
            $after = $before + $change;

            if ($after < 0 || $after < $stock->reserved_quantity) {
                throw new DomainException(__('storefront.order_stock_unavailable'));
            }

            $stock->forceFill(['quantity' => $after])->save();
            $stock->inventoryLogs()->create([
                'product_id' => $stock->product_id,
                'product_variant_id' => $stock->product_variant_id,
                'type' => 'order_confirmed',
                'quantity_before' => $before,
                'quantity_change' => $change,
                'quantity_after' => $after,
                'reason' => __('storefront.order_inventory_deduct_reason', ['order' => $order->order_code]),
                'note' => 'Order creation',
                'created_by' => null,
            ]);
        }
    }

    private function recordCouponUsage(Order $order, CheckoutSession $session): void
    {
        $couponId = $session->coupon_snapshot['id'] ?? null;
        if (! $couponId) {
            return;
        }

        $coupon = Coupon::query()->lockForUpdate()->find($couponId);
        if (! $coupon) {
            return;
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw new DomainException(__('storefront.coupon_usage_limit'));
        }

        if ($session->user_id && $coupon->usage_limit_per_user !== null) {
            $usedByUser = $coupon->usages()->where('user_id', $session->user_id)->count();
            if ($usedByUser >= $coupon->usage_limit_per_user) {
                throw new DomainException(__('storefront.coupon_usage_limit_per_user'));
            }
        }

        $coupon->usages()->create([
            'user_id' => $session->user_id,
            'order_id' => $order->id,
            'cart_id' => $session->cart_id,
            'coupon_code' => $session->coupon_snapshot['code'] ?? $coupon->code,
            'discount_amount' => $session->coupon_snapshot['discount_amount'] ?? $session->discount_amount,
            'used_at' => now(),
        ]);
        $coupon->increment('used_count');
    }

    private function optionValuesSnapshot(?ProductVariant $variant): ?array
    {
        if (! $variant) {
            return null;
        }

        return $variant->optionValues
            ->map(fn ($value): array => [
                'option_id' => $value->option->id,
                'option_name' => $value->option->name,
                'option_label' => $value->option->label(),
                'value_id' => $value->id,
                'value' => $value->value,
                'value_label' => $value->label(),
                'color_code' => $value->color_code,
            ])
            ->values()
            ->all();
    }

    private function generateOrderCode(): string
    {
        $prefix = (string) $this->settings->get('order_code_prefix', 'ORD');

        do {
            $code = $prefix.now()->format('YmdHis').Str::upper(Str::random(6));
        } while (Order::query()->where('order_code', $code)->exists());

        return $code;
    }

    private function addressLine(array $address): string
    {
        return collect([
            $address['address_line'] ?? null,
            $address['ward'] ?? null,
            $address['district'] ?? null,
            $address['province'] ?? null,
            $address['country_code'] ?? null,
        ])->filter()->implode(', ');
    }
}
