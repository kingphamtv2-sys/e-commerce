<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Payments\Gateways\MockPaymentGateway;
use App\Services\OnlinePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MockPaymentGatewayController extends Controller
{
    public function show(Request $request, OnlinePaymentService $service): View
    {
        $transaction = PaymentTransaction::query()
            ->where('transaction_number', $request->string('transaction_number'))
            ->firstOrFail();
        abort_unless(hash_equals(
            (string) ($transaction->request_payload['signature'] ?? ''),
            $request->string('signature')->toString(),
        ), 403);

        return view('storefront.payment.mock', compact('transaction'));
    }

    public function complete(
        Request $request,
        PaymentTransaction $transaction,
        string $status,
        OnlinePaymentService $service,
        MockPaymentGateway $gateway,
    ): RedirectResponse {
        abort_unless(in_array($status, ['paid', 'failed', 'cancelled'], true), 404);
        abort_unless(hash_equals(
            (string) ($transaction->request_payload['signature'] ?? ''),
            $request->string('signature')->toString(),
        ), 403);
        $payload = $gateway->signedResult($transaction, $service->method(), $status);

        return redirect()->route('payment.return', ['gateway' => 'mock'] + $payload);
    }
}
