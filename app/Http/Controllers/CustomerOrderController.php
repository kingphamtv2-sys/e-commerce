<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\CustomerOrderFilterRequest;
use App\Models\Order;
use App\Services\OrderEmailDataService;
use App\Services\StorefrontPageDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function index(
        CustomerOrderFilterRequest $request,
        StorefrontPageDataService $pages,
        OrderEmailDataService $presenter,
    ): View {
        $filters = $request->validated();
        $orders = $request->user()->orders()
            ->when($filters['q'] ?? null, fn ($query, $q) => $query->where('order_code', 'like', '%'.$q.'%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('order_status', $status))
            ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('placed_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('placed_at', '<=', $date))
            ->latest('placed_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('account.orders.index', $pages->data($request, [
            'orders' => $orders,
            'orderFilters' => $filters,
            'money' => fn (Order $order, mixed $amount): string => $presenter->formatMoney($order, $amount),
        ]));
    }

    public function show(
        Request $request,
        int $order,
        StorefrontPageDataService $pages,
        OrderEmailDataService $presenter,
    ): View {
        $order = $request->user()->orders()
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
            ->findOrFail($order);

        return view('account.orders.show', $pages->data($request, [
            'order' => $order,
            'money' => fn (mixed $amount): string => $presenter->formatMoney($order, $amount),
            'guestView' => false,
        ]));
    }
}
