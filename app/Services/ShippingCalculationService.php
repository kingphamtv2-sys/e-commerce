<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use DomainException;
use Illuminate\Support\Collection;

class ShippingCalculationService
{
    public function __construct(
        private readonly CurrencyService $currencyService,
    ) {}

    /** @return Collection<int, array<string, mixed>> */
    public function availableMethods(array $address, float $eligibleSubtotal, Currency $currency, Currency $baseCurrency): Collection
    {
        return ShippingMethod::query()
            ->with('zone')
            ->active()
            ->where(function ($query): void {
                $query->whereNull('shipping_zone_id')
                    ->orWhereHas('zone', fn ($zone) => $zone->active());
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (ShippingMethod $method): bool => $this->methodMatches($method, $address, $eligibleSubtotal))
            ->map(fn (ShippingMethod $method): array => $this->methodSummary($method, $eligibleSubtotal, $currency, $baseCurrency))
            ->values();
    }

    public function calculate(ShippingMethod $method, array $address, float $eligibleSubtotal, Currency $currency, Currency $baseCurrency): array
    {
        $method->loadMissing('zone');

        if (! $this->methodMatches($method, $address, $eligibleSubtotal)) {
            throw new DomainException(__('storefront.shipping_method_unavailable'));
        }

        return $this->methodSummary($method, $eligibleSubtotal, $currency, $baseCurrency);
    }

    public function snapshot(array $methodSummary): array
    {
        return [
            'shipping_method_id' => $methodSummary['id'],
            'shipping_method_code' => $methodSummary['code'],
            'shipping_method_name' => $methodSummary['name'],
            'shipping_method_description' => $methodSummary['description'],
            'shipping_zone_id' => $methodSummary['zone_id'],
            'shipping_zone_name' => $methodSummary['zone_name'],
            'base_shipping_amount' => $methodSummary['base_shipping_amount'],
            'shipping_amount' => $methodSummary['shipping_amount'],
            'shipping_estimated_delivery' => $methodSummary['estimated_delivery'],
        ];
    }

    public function emptySnapshot(): array
    {
        return [
            'shipping_method_id' => null,
            'shipping_method_code' => null,
            'shipping_method_name' => null,
            'shipping_method_description' => null,
            'shipping_zone_id' => null,
            'shipping_zone_name' => null,
            'base_shipping_amount' => 0,
            'shipping_amount' => 0,
            'shipping_estimated_delivery' => null,
        ];
    }

    private function methodMatches(ShippingMethod $method, array $address, float $eligibleSubtotal): bool
    {
        if ($method->status !== ShippingMethod::STATUS_ACTIVE) {
            return false;
        }

        if ($method->zone) {
            if ($method->zone->status !== ShippingZone::STATUS_ACTIVE || ! $this->zoneMatches($method->zone, $address)) {
                return false;
            }
        }

        if ($method->min_order_amount !== null && $eligibleSubtotal < (float) $method->min_order_amount) {
            return false;
        }

        if ($method->max_order_amount !== null && $eligibleSubtotal > (float) $method->max_order_amount) {
            return false;
        }

        if ($method->type === ShippingMethod::TYPE_FREE_SHIPPING && $method->free_shipping_min_amount !== null) {
            return $eligibleSubtotal >= (float) $method->free_shipping_min_amount;
        }

        return true;
    }

    private function zoneMatches(ShippingZone $zone, array $address): bool
    {
        return $this->valueMatches($zone->countries, strtoupper((string) ($address['country_code'] ?? '')))
            && $this->valueMatches($zone->cities, (string) ($address['province'] ?? ''))
            && $this->valueMatches($zone->districts, (string) ($address['district'] ?? ''));
    }

    /** @param array<int, string>|null $values */
    private function valueMatches(?array $values, string $needle): bool
    {
        if (! $values) {
            return true;
        }

        $needle = mb_strtolower(trim($needle));
        if ($needle === '') {
            return false;
        }

        return collect($values)->contains(fn (string $value): bool => mb_strtolower(trim($value)) === $needle);
    }

    private function methodSummary(ShippingMethod $method, float $eligibleSubtotal, Currency $currency, Currency $baseCurrency): array
    {
        $baseAmount = $this->baseAmount($method, $eligibleSubtotal);
        $displayAmount = $this->currencyService->convert($baseAmount, $baseCurrency, $currency);

        return [
            'id' => $method->id,
            'code' => $method->code,
            'name' => $method->name,
            'description' => $method->description,
            'type' => $method->type,
            'zone_id' => $method->shipping_zone_id,
            'zone_name' => $method->zone?->name,
            'base_shipping_amount' => round($baseAmount, 2),
            'shipping_amount' => round($baseAmount, 2),
            'formatted_shipping_amount' => $baseAmount <= 0
                ? __('storefront.shipping_free')
                : $this->currencyService->format($displayAmount, $currency),
            'estimated_delivery' => $this->estimatedDelivery($method),
            'free_shipping_min_amount' => $method->free_shipping_min_amount,
            'min_order_amount' => $method->min_order_amount,
            'max_order_amount' => $method->max_order_amount,
        ];
    }

    private function baseAmount(ShippingMethod $method, float $eligibleSubtotal): float
    {
        if (in_array($method->type, [ShippingMethod::TYPE_FREE_SHIPPING, ShippingMethod::TYPE_PICKUP], true)) {
            return 0;
        }

        if ($method->free_shipping_min_amount !== null && $eligibleSubtotal >= (float) $method->free_shipping_min_amount) {
            return 0;
        }

        return (float) $method->base_fee;
    }

    private function estimatedDelivery(ShippingMethod $method): ?string
    {
        $min = $method->estimated_delivery_min_days;
        $max = $method->estimated_delivery_max_days;

        if ($min === null && $max === null) {
            return null;
        }

        if ($min !== null && $max !== null && $min !== $max) {
            return __('storefront.shipping_estimate_range', ['min' => $min, 'max' => $max]);
        }

        return __('storefront.shipping_estimate_single', ['days' => $min ?? $max]);
    }
}
