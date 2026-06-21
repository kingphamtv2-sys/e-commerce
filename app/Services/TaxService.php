<?php

namespace App\Services;

use App\Models\TaxClass;
use App\Models\TaxRate;
use Illuminate\Support\Facades\Cache;

class TaxService
{
    public const CACHE_CLASSES = 'tax_classes';

    public const CACHE_RATES = 'tax_rates';

    /** @return array<int, TaxClass> */
    public function classes(): array
    {
        return Cache::rememberForever(
            self::CACHE_CLASSES,
            static fn (): array => TaxClass::query()->withCount('taxRates')->orderBy('name')->get()->all(),
        );
    }

    /** @return array<int, TaxRate> */
    public function rates(): array
    {
        return Cache::rememberForever(
            self::CACHE_RATES,
            static fn (): array => TaxRate::query()
                ->with('taxClass')
                ->orderBy('priority')
                ->orderBy('country_code')
                ->orderBy('region')
                ->get()
                ->all(),
        );
    }

    public function findRate(TaxClass|int|string $taxClass, ?string $countryCode = null, ?string $region = null): ?TaxRate
    {
        $resolved = $this->resolveTaxClass($taxClass);

        if (! $resolved?->status) {
            return null;
        }

        $countryCode = $countryCode ? strtoupper(trim($countryCode)) : null;
        $region = $region ? trim($region) : null;

        return collect($this->rates())
            ->filter(fn (TaxRate $rate): bool => $rate->status && $rate->tax_class_id === $resolved->id)
            ->filter(fn (TaxRate $rate): bool => $rate->country_code === null || $rate->country_code === $countryCode)
            ->filter(fn (TaxRate $rate): bool => $rate->region === null || $rate->region === $region)
            ->sortBy([
                [fn (TaxRate $rate): int => $rate->country_code === $countryCode ? 0 : 1, 'asc'],
                [fn (TaxRate $rate): int => $rate->region === $region ? 0 : 1, 'asc'],
                ['priority', 'asc'],
            ])
            ->first();
    }

    /** @return array{rate: float, base_amount: float, tax_amount: float, total_amount: float} */
    public function calculate(
        float|int $amount,
        TaxClass|int|string $taxClass,
        ?string $countryCode = null,
        ?string $region = null,
    ): array {
        $settings = app(SystemSettingService::class);
        $taxEnabled = filter_var($settings->get('tax_enabled', false), FILTER_VALIDATE_BOOL);
        $priceIncludesTax = filter_var($settings->get('price_include_tax', false), FILTER_VALIDATE_BOOL);
        $rate = $taxEnabled ? (float) ($this->findRate($taxClass, $countryCode, $region)?->rate ?? 0) : 0.0;
        $amount = (float) $amount;

        if ($rate <= 0) {
            return $this->result(0, $amount, 0, $amount);
        }

        if ($priceIncludesTax) {
            $baseAmount = $amount / (1 + ($rate / 100));

            return $this->result($rate, $baseAmount, $amount - $baseAmount, $amount);
        }

        $taxAmount = $amount * ($rate / 100);

        return $this->result($rate, $amount, $taxAmount, $amount + $taxAmount);
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_CLASSES);
        Cache::forget(self::CACHE_RATES);
    }

    private function resolveTaxClass(TaxClass|int|string $taxClass): ?TaxClass
    {
        if ($taxClass instanceof TaxClass) {
            return $taxClass;
        }

        return is_int($taxClass) || ctype_digit($taxClass)
            ? TaxClass::query()->find((int) $taxClass)
            : TaxClass::query()->where('code', strtolower($taxClass))->first();
    }

    /** @return array{rate: float, base_amount: float, tax_amount: float, total_amount: float} */
    private function result(float $rate, float $baseAmount, float $taxAmount, float $totalAmount): array
    {
        return [
            'rate' => $rate,
            'base_amount' => round($baseAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }
}
