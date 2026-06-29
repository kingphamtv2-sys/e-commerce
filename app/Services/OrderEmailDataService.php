<?php

namespace App\Services;

use App\Models\Order;

class OrderEmailDataService
{
    public function data(Order $order): array
    {
        $order->loadMissing(['orderItems', 'orderAddresses', 'orderPayments', 'payment']);

        return [
            'order' => $order,
            'items' => $order->orderItems,
            'shipping' => $order->orderAddresses->firstWhere('type', 'shipping'),
            'billing' => $order->orderAddresses->firstWhere('type', 'billing'),
            'payment' => $order->orderPayments->first(),
            'money' => fn (float|int|string|null $amount): string => $this->formatMoney($order, $amount),
            'customerUrl' => $order->user_id
                ? route('account.orders.show', $order)
                : route('guest.orders.show', $order->success_token),
            'adminUrl' => route('admin.orders.show', $order),
        ];
    }

    public function formatMoney(Order $order, float|int|string|null $amount): string
    {
        $snapshot = $order->currency_snapshot ?? [];
        $decimals = (int) ($order->currency_decimal_places ?? $snapshot['decimal_places'] ?? 0);
        $decimalSeparator = (string) ($snapshot['decimal_separator'] ?? '.');
        $thousandSeparator = (string) ($snapshot['thousand_separator'] ?? ',');
        $symbol = (string) ($order->currency_symbol ?: ($snapshot['symbol'] ?? $order->currency_code));
        $position = (string) ($order->currency_symbol_position ?: ($snapshot['symbol_position'] ?? 'after'));
        $formatted = number_format((float) $amount, $decimals, $decimalSeparator, $thousandSeparator);

        return $position === 'before' ? $symbol.$formatted : $formatted.' '.$symbol;
    }
}
