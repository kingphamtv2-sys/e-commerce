<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\User;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CouponService
{
    public function normalizeCode(string $code): string
    {
        return Str::upper(trim($code));
    }

    public function create(array $data): Coupon
    {
        return DB::transaction(function () use ($data): Coupon {
            $coupon = Coupon::query()->create($this->couponData($data));
            $this->syncRestrictions($coupon, $data);

            return $coupon->refresh()->load(['categories', 'products']);
        });
    }

    public function update(Coupon $coupon, array $data): Coupon
    {
        return DB::transaction(function () use ($coupon, $data): Coupon {
            $coupon->update($this->couponData($data));
            $this->syncRestrictions($coupon, $data);

            return $coupon->refresh()->load(['categories', 'products']);
        });
    }

    public function deleteOrDisable(Coupon $coupon): string
    {
        if ($coupon->usages()->exists()) {
            $coupon->update(['status' => Coupon::STATUS_INACTIVE]);

            return __('admin.coupons.disabled_used');
        }

        $coupon->delete();

        return __('admin.coupons.deleted');
    }

    public function applyToCart(Cart $cart, string $code, ?User $user, Collection $items): array
    {
        $coupon = Coupon::query()
            ->with(['categories:id', 'products:id'])
            ->where('code', $this->normalizeCode($code))
            ->first();

        if (! $coupon) {
            throw new DomainException(__('storefront.coupon_not_found'));
        }

        $calculation = $this->calculate($coupon, $items, $user);
        $cart->forceFill([
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'coupon_discount_amount' => $calculation['discount_amount'],
        ])->save();

        return [
            ...$calculation,
            'coupon' => $coupon,
        ];
    }

    public function removeFromCart(?Cart $cart): void
    {
        $cart?->forceFill([
            'coupon_id' => null,
            'coupon_code' => null,
            'coupon_discount_amount' => 0,
        ])->save();
    }

    public function revalidateCart(?Cart $cart, ?User $user, Collection $items): array
    {
        if (! $cart || ! $cart->coupon_id) {
            return $this->emptyResult();
        }

        $coupon = Coupon::query()->with(['categories:id', 'products:id'])->find($cart->coupon_id);
        if (! $coupon) {
            $this->removeFromCart($cart);

            return $this->emptyResult();
        }

        try {
            $calculation = $this->calculate($coupon, $items, $user);
        } catch (DomainException) {
            $this->removeFromCart($cart);

            return $this->emptyResult();
        }

        $cart->forceFill([
            'coupon_code' => $coupon->code,
            'coupon_discount_amount' => $calculation['discount_amount'],
        ])->save();

        return [
            ...$calculation,
            'coupon' => $coupon,
        ];
    }

    public function calculate(Coupon $coupon, Collection $items, ?User $user): array
    {
        $this->assertCouponUsable($coupon, $user);

        $eligibleItems = $items->filter(fn (array $item): bool => $this->itemEligible($coupon, $item));
        $eligibleSubtotal = (float) $eligibleItems->sum('subtotal');

        if ($items->isEmpty()) {
            throw new DomainException(__('storefront.coupon_cart_empty'));
        }
        if ($eligibleSubtotal <= 0) {
            throw new DomainException(__('storefront.coupon_no_eligible_items'));
        }

        $minOrderAmount = (float) ($coupon->min_order_amount ?? 0);
        if ($minOrderAmount > 0 && $eligibleSubtotal < $minOrderAmount) {
            throw new DomainException(__('storefront.coupon_min_order', ['amount' => number_format($minOrderAmount)]));
        }

        $discount = $coupon->discount_type === Coupon::TYPE_PERCENTAGE
            ? $eligibleSubtotal * ((float) $coupon->discount_value / 100)
            : (float) $coupon->discount_value;

        if ($coupon->discount_type === Coupon::TYPE_PERCENTAGE && $coupon->max_discount_amount !== null) {
            $discount = min($discount, (float) $coupon->max_discount_amount);
        }

        $discount = round(min($discount, $eligibleSubtotal), 2);

        return [
            'eligible_subtotal' => $eligibleSubtotal,
            'discount_amount' => max(0, $discount),
        ];
    }

    private function assertCouponUsable(Coupon $coupon, ?User $user): void
    {
        if ($coupon->status !== Coupon::STATUS_ACTIVE) {
            throw new DomainException(__('storefront.coupon_inactive'));
        }
        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw new DomainException(__('storefront.coupon_not_started'));
        }
        if ($coupon->ends_at && $coupon->ends_at->isPast()) {
            throw new DomainException(__('storefront.coupon_expired'));
        }
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw new DomainException(__('storefront.coupon_usage_limit'));
        }
        if ($user && $coupon->usage_limit_per_user !== null) {
            $usedByUser = $coupon->usages()->where('user_id', $user->id)->count();
            if ($usedByUser >= $coupon->usage_limit_per_user) {
                throw new DomainException(__('storefront.coupon_usage_limit_per_user'));
            }
        }
    }

    private function itemEligible(Coupon $coupon, array $item): bool
    {
        if (! ($item['available'] ?? false)) {
            return false;
        }

        $product = $item['product'];
        if ($coupon->relationLoaded('products') && $coupon->products->isNotEmpty()) {
            return $coupon->products->contains('id', $product->id);
        }

        if ($coupon->relationLoaded('categories') && $coupon->categories->isNotEmpty()) {
            return $coupon->categories->contains('id', $product->category_id);
        }

        return true;
    }

    private function couponData(array $data): array
    {
        return [
            'code' => $this->normalizeCode($data['code']),
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'max_discount_amount' => $data['discount_type'] === Coupon::TYPE_PERCENTAGE ? ($data['max_discount_amount'] ?? null) : null,
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'usage_limit_per_user' => $data['usage_limit_per_user'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'status' => $data['status'],
        ];
    }

    private function syncRestrictions(Coupon $coupon, array $data): void
    {
        $coupon->categories()->sync($data['categories'] ?? []);
        $coupon->products()->sync($data['products'] ?? []);
    }

    private function emptyResult(): array
    {
        return [
            'eligible_subtotal' => 0.0,
            'discount_amount' => 0.0,
            'coupon' => null,
        ];
    }
}
