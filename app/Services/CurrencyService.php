<?php

namespace App\Services;

use App\Models\Currency;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CurrencyService
{
    public const CACHE_ALL = 'currencies_all';

    public const CACHE_ACTIVE = 'currencies_active';

    public const CACHE_DEFAULT = 'currency_default';

    /** @return array<int, Currency> */
    public function all(): array
    {
        return Cache::rememberForever(
            self::CACHE_ALL,
            static fn (): array => Currency::query()->orderByDesc('is_default')->orderBy('code')->get()->all(),
        );
    }

    /** @return array<int, Currency> */
    public function active(): array
    {
        return Cache::rememberForever(
            self::CACHE_ACTIVE,
            static fn (): array => Currency::query()->active()->orderByDesc('is_default')->orderBy('code')->get()->all(),
        );
    }

    public function getDefault(): ?Currency
    {
        return Cache::rememberForever(
            self::CACHE_DEFAULT,
            static fn (): ?Currency => Currency::query()->default()->first(),
        );
    }

    public function findByCode(string $code): ?Currency
    {
        return collect($this->all())->firstWhere('code', strtoupper($code));
    }

    public function setDefault(Currency $currency): void
    {
        if (! $currency->status) {
            throw new DomainException(__('admin.messages.inactive_currency_cannot_default'));
        }

        DB::transaction(function () use ($currency): void {
            Currency::query()->where('id', '!=', $currency->getKey())->update(['is_default' => false]);
            $currency->forceFill(['is_default' => true])->save();
        });

        $this->clearCache();
    }

    public function convert(float|int $amount, Currency|string $from, Currency|string $to): float
    {
        $source = $this->resolve($from);
        $target = $this->resolve($to);

        if ((float) $source->exchange_rate <= 0 || (float) $target->exchange_rate <= 0) {
            throw new DomainException('Currency exchange rate must be greater than zero.');
        }

        $baseAmount = (float) $amount * (float) $source->exchange_rate;

        return round($baseAmount / (float) $target->exchange_rate, $target->decimal_places);
    }

    public function format(float|int $amount, Currency|string $currency): string
    {
        $resolved = $this->resolve($currency);
        $formatted = number_format(
            (float) $amount,
            $resolved->decimal_places,
            $resolved->decimal_separator ?? '.',
            $resolved->thousand_separator ?? '',
        );

        return $resolved->symbol_position === 'before'
            ? $resolved->symbol.$formatted
            : $formatted.' '.$resolved->symbol;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_ALL);
        Cache::forget(self::CACHE_ACTIVE);
        Cache::forget(self::CACHE_DEFAULT);
    }

    private function resolve(Currency|string $currency): Currency
    {
        if ($currency instanceof Currency) {
            return $currency;
        }

        return $this->findByCode($currency)
            ?? throw new InvalidArgumentException("Currency [{$currency}] was not found.");
    }
}
