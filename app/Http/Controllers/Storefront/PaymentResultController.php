<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OnlinePaymentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentResultController extends Controller
{
    public function show(Request $request, Order $order, OnlinePaymentService $service): View
    {
        abort_unless(hash_equals($order->success_token, (string) $request->query('token')), 403);
        $service->assertOrderOwnership($request, $order->load(['paymentTransactions', 'checkoutSession']));

        return view('storefront.payment.result', [
            'order' => $order,
            'transaction' => $order->paymentTransactions->sortByDesc('id')->first(),
        ]);
    }

    public function error(): View
    {
        return view('storefront.payment.error');
    }
}
