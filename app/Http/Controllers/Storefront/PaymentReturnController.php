<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\OnlinePaymentService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentReturnController extends Controller
{
    public function __invoke(Request $request, string $gateway, OnlinePaymentService $service): RedirectResponse
    {
        try {
            $transaction = $service->processReturn($gateway, $request->query());
        } catch (DomainException $exception) {
            return redirect()->route('payment.error')->withErrors(['payment' => $exception->getMessage()]);
        }

        return redirect()->route('payment.result', [
            'order' => $transaction->order_id,
            'token' => $transaction->order->success_token,
        ]);
    }
}
