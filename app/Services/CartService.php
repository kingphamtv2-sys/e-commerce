<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Currency;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantImage;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartService
{
    public function currentCart(Request $request, bool $create = false): ?Cart
    {
        $user = $request->user();
        $sessionId = $this->sessionIdentifier($request);

        $query = Cart::query()->where('status', 'active');
        $cart = $user
            ? (clone $query)->where('user_id', $user->id)->latest()->first()
            : (clone $query)->where('session_id', $sessionId)->latest()->first();

        if ($cart || ! $create) {
            return $cart?->load($this->cartRelations());
        }

        return Cart::query()->create([
            'user_id' => $user?->id,
            'session_id' => $user ? null : $sessionId,
            'status' => 'active',
            'currency_code' => $request->session()->get('storefront_currency'),
            'expires_at' => $user ? null : now()->addDays(30),
        ])->load($this->cartRelations());
    }

    public function add(Request $request, int $productId, ?int $variantId, int $quantity): CartItem
    {
        return DB::transaction(function () use ($request, $productId, $variantId, $quantity): CartItem {
            $product = $this->findPurchasableProduct($productId);
            $variant = $this->resolveVariant($product, $variantId);
            $stock = $this->stockFor($product, $variant);
            $cart = $this->currentCart($request, true);

            $item = $cart->cartItems()
                ->where('product_id', $product->id)
                ->where(function (Builder $query) use ($variant): void {
                    $variant
                        ? $query->where('product_variant_id', $variant->id)
                        : $query->whereNull('product_variant_id');
                })
                ->first();

            $newQuantity = ($item?->quantity ?? 0) + $quantity;
            $this->assertQuantity($newQuantity, $stock);
            $unitPrice = $this->unitPrice($product, $variant);

            if ($item) {
                $item->update(['quantity' => $newQuantity, 'unit_price' => $unitPrice, 'price' => $unitPrice]);

                return $item->refresh()->load($this->itemRelations());
            }

            return $cart->cartItems()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => $newQuantity,
                'unit_price' => $unitPrice,
                'price' => $unitPrice,
            ])->load($this->itemRelations());
        });
    }

    public function updateQuantity(Request $request, CartItem $item, int $quantity): CartItem
    {
        $this->assertOwnsItem($request, $item);
        $item->load($this->itemRelations());
        $product = $item->product;
        $variant = $item->productVariant;
        $this->assertItemPurchasable($product, $variant);
        $stock = $this->stockFor($product, $variant);
        $this->assertQuantity($quantity, $stock);
        $unitPrice = $this->unitPrice($product, $variant);
        $item->update(['quantity' => $quantity, 'unit_price' => $unitPrice, 'price' => $unitPrice]);

        return $item->refresh()->load($this->itemRelations());
    }

    public function remove(Request $request, CartItem $item): void
    {
        $this->assertOwnsItem($request, $item);
        $item->delete();
    }

    public function clear(Request $request): void
    {
        $cart = $this->currentCart($request);
        $cart?->cartItems()->delete();
        app(CouponService::class)->removeFromCart($cart);
    }

    public function mergeGuestCartIntoUser(string $guestSessionId, User $user): void
    {
        $guestCart = Cart::query()->where('status', 'active')->where('session_id', $guestSessionId)->with($this->cartRelations())->latest()->first();
        if (! $guestCart || $guestCart->cartItems->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($guestCart, $user): void {
            $customerCart = Cart::query()->where('status', 'active')->where('user_id', $user->id)->with($this->cartRelations())->latest()->first();
            if (! $customerCart) {
                $guestCart->update(['user_id' => $user->id, 'session_id' => null, 'expires_at' => null]);

                return;
            }

            foreach ($guestCart->cartItems as $guestItem) {
                try {
                    $this->assertItemPurchasable($guestItem->product, $guestItem->productVariant);
                    $stock = $this->stockFor($guestItem->product, $guestItem->productVariant);
                    $existing = $customerCart->cartItems()
                        ->where('product_id', $guestItem->product_id)
                        ->where(function (Builder $query) use ($guestItem): void {
                            $guestItem->product_variant_id
                                ? $query->where('product_variant_id', $guestItem->product_variant_id)
                                : $query->whereNull('product_variant_id');
                        })
                        ->first();
                    $quantity = min($stock->availableQuantity(), ($existing?->quantity ?? 0) + $guestItem->quantity);
                    if ($quantity < 1) {
                        continue;
                    }
                    $unitPrice = $this->unitPrice($guestItem->product, $guestItem->productVariant);
                    $existing
                        ? $existing->update(['quantity' => $quantity, 'unit_price' => $unitPrice, 'price' => $unitPrice])
                        : $customerCart->cartItems()->create([
                            'product_id' => $guestItem->product_id,
                            'product_variant_id' => $guestItem->product_variant_id,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'price' => $unitPrice,
                        ]);
                } catch (DomainException) {
                    continue;
                }
            }

            $guestCart->update(['status' => 'abandoned']);
            $guestCart->cartItems()->delete();
        });
    }

    public function count(?Cart $cart): int
    {
        return $cart?->cartItems->sum('quantity') ?? 0;
    }

    public function summary(?Cart $cart, Currency $currency, Currency $baseCurrency): array
    {
        $items = $cart ? $this->items($cart, $currency, $baseCurrency) : collect();
        $subtotal = $items->sum('subtotal');
        $coupon = app(CouponService::class)->revalidateCart($cart, $cart?->user, $items);
        $discount = (float) $coupon['discount_amount'];
        $estimatedTotal = max(0, $subtotal - $discount);
        $appliedCoupon = $coupon['coupon'] ?? null;

        return [
            'total_items' => $items->sum('quantity'),
            'subtotal' => $subtotal,
            'formatted_subtotal' => $this->formatPrice($subtotal, $currency, $baseCurrency),
            'discount_amount' => $discount,
            'formatted_discount_amount' => $this->formatPrice($discount, $currency, $baseCurrency),
            'estimated_total' => $estimatedTotal,
            'formatted_estimated_total' => $this->formatPrice($estimatedTotal, $currency, $baseCurrency),
            'applied_coupon' => $appliedCoupon ? [
                'id' => $appliedCoupon->id,
                'code' => $appliedCoupon->code,
                'discount_amount' => $discount,
                'formatted_discount_amount' => $this->formatPrice($discount, $currency, $baseCurrency),
            ] : null,
            'currency_code' => $currency->code,
            'is_empty' => $items->isEmpty(),
            'has_unavailable' => $items->contains(fn (array $item): bool => ! $item['available']),
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    public function items(Cart $cart, Currency $currency, Currency $baseCurrency): Collection
    {
        return $cart->load($this->cartRelations())->cartItems->map(function (CartItem $item) use ($currency, $baseCurrency): array {
            $product = $item->product;
            $variant = $item->productVariant;
            $available = $this->itemAvailability($product, $variant);
            $stock = $available ? $this->stockFor($product, $variant) : null;
            $unitPrice = $available ? $this->unitPrice($product, $variant) : (float) ($item->unit_price ?: $item->price);
            $subtotal = $unitPrice * $item->quantity;

            return [
                'model' => $item,
                'id' => $item->id,
                'product' => $product,
                'variant' => $variant,
                'name' => app(ProductService::class)->name($product, app()->getLocale()),
                'sku' => $variant?->sku ?: $product->sku,
                'variant_label' => $this->variantLabel($variant),
                'image_url' => $this->itemImageUrl($product, $variant),
                'quantity' => $item->quantity,
                'available_quantity' => $stock?->availableQuantity() ?? 0,
                'available' => $available && ($stock?->availableQuantity() ?? 0) >= $item->quantity,
                'availability_message' => $this->availabilityMessage($product, $variant, $stock, $item->quantity),
                'unit_price' => $unitPrice,
                'formatted_unit_price' => $this->formatPrice($unitPrice, $currency, $baseCurrency),
                'subtotal' => $subtotal,
                'formatted_subtotal' => $this->formatPrice($subtotal, $currency, $baseCurrency),
            ];
        });
    }

    public function responsePayload(Request $request, ?CartItem $item = null): array
    {
        [$currency, $baseCurrency] = $this->currencies($request);
        $cart = $this->currentCart($request);
        $summary = $this->summary($cart, $currency, $baseCurrency);
        $payload = ['cart_count' => $summary['total_items'], 'cart_subtotal' => $summary['formatted_subtotal'], 'summary' => $summary];

        if ($item) {
            $item->load($this->itemRelations());
            $viewItem = $this->items($item->cart, $currency, $baseCurrency)->firstWhere('id', $item->id);
            $payload['item'] = [
                'id' => $viewItem['id'],
                'quantity' => $viewItem['quantity'],
                'available_quantity' => $viewItem['available_quantity'],
                'unit_price' => $viewItem['formatted_unit_price'],
                'subtotal' => $viewItem['formatted_subtotal'],
            ];
            $payload['cart_row_html'] = view('storefront.cart._item', ['item' => $viewItem])->render();
            $payload['item_subtotal'] = $viewItem['formatted_subtotal'];
            $payload['item_quantity'] = $viewItem['quantity'];
            $payload['available_quantity'] = $viewItem['available_quantity'];
        }

        return $payload;
    }

    /** @return array{0: Currency, 1: Currency} */
    public function currencies(Request $request): array
    {
        $currencyService = app(CurrencyService::class);
        $baseCurrency = $currencyService->getDefault() ?? Currency::query()->active()->firstOrFail();
        $currency = $request->session()->get('storefront_currency')
            ? $currencyService->findByCode($request->session()->get('storefront_currency'))
            : null;

        return [$currency?->status ? $currency : $baseCurrency, $baseCurrency];
    }

    /** @return array<int, string> */
    private function cartRelations(): array
    {
        return ['user', 'coupon', 'cartItems' => fn ($query) => $query->latest(), ...$this->itemRelations('cartItems.')];
    }

    /** @return array<int, string> */
    private function itemRelations(string $prefix = ''): array
    {
        return [
            $prefix.'cart',
            $prefix.'product.productTranslations',
            $prefix.'product.category',
            $prefix.'product.taxClass',
            $prefix.'product.productVariants',
            $prefix.'product.productImages' => fn ($query) => $query->active()->orderByDesc('is_main')->orderBy('sort_order')->orderBy('id'),
            $prefix.'product.inventoryStocks',
            $prefix.'productVariant.optionValues.option',
            $prefix.'productVariant.variantImages' => fn ($query) => $query->active()->orderByDesc('is_main')->orderBy('sort_order')->orderBy('id'),
            $prefix.'productVariant.inventoryStock',
        ];
    }

    private function findPurchasableProduct(int $id): Product
    {
        $product = Product::query()->with(['category', 'productVariants' => fn ($query) => $query->where('status', true), 'inventoryStocks'])->find($id);
        if (! $product || ! $product->status || ! $product->category?->status) {
            throw new DomainException(__('storefront.cart_product_unavailable'));
        }

        return $product;
    }

    private function resolveVariant(Product $product, ?int $variantId): ?ProductVariant
    {
        $hasVariants = $product->productVariants()->where('status', true)->exists();
        if ($hasVariants && ! $variantId) {
            throw new DomainException(__('storefront.cart_variant_required'));
        }
        if (! $hasVariants) {
            return null;
        }

        $variant = $product->productVariants()->whereKey($variantId)->first();
        if (! $variant || ! $variant->status) {
            throw new DomainException(__('storefront.cart_variant_unavailable'));
        }

        return $variant;
    }

    private function assertItemPurchasable(Product $product, ?ProductVariant $variant): void
    {
        if (! $product->status || ! $product->category?->status) {
            throw new DomainException(__('storefront.cart_product_unavailable'));
        }
        if ($variant && (! $variant->status || $variant->product_id !== $product->id)) {
            throw new DomainException(__('storefront.cart_variant_unavailable'));
        }
    }

    private function itemAvailability(Product $product, ?ProductVariant $variant): bool
    {
        try {
            $this->assertItemPurchasable($product, $variant);

            return true;
        } catch (DomainException) {
            return false;
        }
    }

    private function stockFor(Product $product, ?ProductVariant $variant): InventoryStock
    {
        $stock = $variant
            ? $product->inventoryStocks->firstWhere('product_variant_id', $variant->id) ?? $variant->inventoryStock
            : $product->inventoryStocks->first(fn (InventoryStock $stock): bool => $stock->product_variant_id === null);

        if (! $stock || $stock->availableQuantity() < 1) {
            throw new DomainException(__('storefront.cart_out_of_stock'));
        }

        return $stock;
    }

    private function assertQuantity(int $quantity, InventoryStock $stock): void
    {
        if ($quantity < 1) {
            throw new DomainException(__('storefront.cart_invalid_quantity'));
        }
        if ($quantity > $stock->availableQuantity()) {
            throw new DomainException(__('storefront.cart_quantity_exceeds', ['count' => $stock->availableQuantity()]));
        }
    }

    private function unitPrice(Product $product, ?ProductVariant $variant): float
    {
        $regularPrice = (float) ($variant?->price ?? $product->price);
        $saleCandidate = $variant?->sale_price ?? $product->sale_price;

        return $saleCandidate !== null && (float) $saleCandidate < $regularPrice
            ? (float) $saleCandidate
            : $regularPrice;
    }

    private function formatPrice(float|int $amount, Currency $currency, Currency $baseCurrency): string
    {
        return app(CatalogService::class)->formatPrice($amount, $currency, $baseCurrency);
    }

    private function variantLabel(?ProductVariant $variant): ?string
    {
        if (! $variant) {
            return null;
        }

        return $variant->optionValues->isNotEmpty()
            ? $variant->optionValues->map(fn ($value): string => $value->option->label().': '.$value->label())->implode(', ')
            : $variant->name;
    }

    private function itemImageUrl(Product $product, ?ProductVariant $variant): ?string
    {
        $image = $variant?->variantImages->first()
            ?? $product->productImages->first();

        return match (true) {
            $image instanceof VariantImage => app(VariantImageService::class)->url($image),
            $image instanceof ProductImage => app(ProductImageService::class)->url($image),
            default => null,
        };
    }

    private function availabilityMessage(Product $product, ?ProductVariant $variant, ?InventoryStock $stock, int $quantity): ?string
    {
        if (! $product->status || ! $product->category?->status) {
            return __('storefront.cart_product_unavailable');
        }
        if ($variant && ! $variant->status) {
            return __('storefront.cart_variant_unavailable');
        }
        if (! $stock || $stock->availableQuantity() < 1) {
            return __('storefront.cart_out_of_stock');
        }
        if ($quantity > $stock->availableQuantity()) {
            return __('storefront.cart_quantity_exceeds', ['count' => $stock->availableQuantity()]);
        }

        return null;
    }

    private function assertOwnsItem(Request $request, CartItem $item): void
    {
        $cart = $this->currentCart($request);
        if (! $cart || $item->cart_id !== $cart->id) {
            throw new DomainException(__('storefront.cart_item_forbidden'));
        }
    }

    private function sessionIdentifier(Request $request): string
    {
        if (! $request->session()->has('cart_session_id')) {
            $request->session()->put('cart_session_id', (string) Str::uuid());
        }

        return (string) $request->session()->get('cart_session_id');
    }
}
