<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class ReportFilterService
{
    public const ORDER_STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'completed', 'cancelled', 'refunded'];

    public const PAYMENT_STATUSES = ['unpaid', 'pending', 'paid', 'failed', 'refunded', 'cancelled'];

    public const PAYMENT_METHODS = ['cod', 'online'];

    public function defaults(array $filters, bool $withDates = true): array
    {
        if (! $withDates) {
            return $filters;
        }

        $today = CarbonImmutable::today();

        return [
            ...$filters,
            'date_from' => ! empty($filters['date_from']) ? $filters['date_from'] : $today->subDays(29)->toDateString(),
            'date_to' => ! empty($filters['date_to']) ? $filters['date_to'] : $today->toDateString(),
        ];
    }

    public function filename(string $report, array $filters): string
    {
        $range = isset($filters['date_from'], $filters['date_to'])
            ? $filters['date_from'].'_'.$filters['date_to']
            : CarbonImmutable::today()->toDateString();

        return "{$report}_{$range}.csv";
    }
}
