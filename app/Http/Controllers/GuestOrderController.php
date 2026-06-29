<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderEmailDataService;
use App\Services\StorefrontPageDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestOrderController extends Controller
{
    public function show(
        Request $request,
        string $token,
        StorefrontPageDataService $pages,
        OrderEmailDataService $presenter,
    ): View {
        $order = Order::query()
            ->whereNull('user_id')
            ->where('success_token', $token)
            ->with([
                'orderItems',
                'orderAddresses',
                'orderPayments',
                'paymentTransactions' => fn ($query) => $query->select([
                    'id',
                    'order_id',
                    'transaction_number',
                    'gateway_code',
                    'status',
                    'amount',
                    'currency_code',
                    'paid_at',
                    'created_at',
                ])->latest(),
                'statusHistories' => fn ($query) => $query->select([
                    'id',
                    'order_id',
                    'from_status',
                    'to_status',
                    'created_at',
                ])->oldest(),
            ])
            ->firstOrFail();

        return view('account.orders.show', $pages->data($request, [
            'order' => $order,
            'money' => fn (mixed $amount): string => $presenter->formatMoney($order, $amount),
            'guestView' => true,
        ]));
    }
}
