<?php

namespace App\Services;

use App\Models\CheckoutSession;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\PaymentWebhookLog;
use App\Payments\PaymentGatewayManager;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class OnlinePaymentService
{
    public const METHOD_CODE = 'online';

    public function __construct(
        private readonly PaymentCodService $checkoutValidator,
        private readonly OrderCreationService $orderCreationService,
        private readonly PaymentGatewayManager $gatewayManager,
    ) {}

    public function method(): PaymentMethod
    {
        return PaymentMethod::query()->firstOrCreate(
            ['code' => self::METHOD_CODE],
            [
                'name' => 'Online Payment',
                'description' => 'Pay securely through our sandbox payment gateway.',
                'instruction' => 'You will be redirected to the payment gateway.',
                'gateway_code' => 'mock',
                'environment' => 'sandbox',
                'sort_order' => 20,
                'status' => 'inactive',
            ],
        );
    }

    public function availability(Request $request, string $token): array
    {
        $session = $this->checkoutValidator->validatedCheckoutSession($request, $token);
        $method = $this->method();
        $available = true;
        $message = null;

        try {
            $this->assertSelectable($request, $session, $method);
        } catch (DomainException $exception) {
            $available = false;
            $message = $exception->getMessage();
        }

        return compact('method', 'available', 'message');
    }

    public function select(Request $request, string $token): CheckoutSession
    {
        return DB::transaction(function () use ($request, $token): CheckoutSession {
            $session = $this->checkoutValidator->validatedCheckoutSession($request, $token, true);
            $method = PaymentMethod::query()->where('code', self::METHOD_CODE)->lockForUpdate()->first() ?? $this->method();
            $this->assertSelectable($request, $session, $method);

            $session->forceFill([
                'payment_method_code' => self::METHOD_CODE,
                'payment_method_name' => $method->name,
                'payment_status' => 'pending',
                'payment_amount' => $session->grand_total,
                'payment_currency_code' => $session->currency_snapshot['code'] ?? 'VND',
                'payment_instruction' => $method->instruction,
                'payment_selected_at' => now(),
            ])->save();

            return $session->refresh();
        });
    }

    /** @return array{order:Order,transaction:PaymentTransaction,redirect_url:string} */
    public function createOrderAndPayment(Request $request, string $token): array
    {
        $completed = CheckoutSession::query()->where('token', $token)->where('status', 'completed')->with('order.checkoutSession')->first();
        if ($completed?->order) {
            $this->assertOrderOwnership($request, $completed->order);
            $existing = $completed->order->paymentTransactions()->whereIn('status', ['pending', 'processing'])->latest()->first();
            if ($existing) {
                $method = $this->method();
                $gatewayResult = $this->gatewayManager->gateway($method->gateway_code)->createPayment($existing, $method);

                return ['order' => $completed->order, 'transaction' => $existing, 'redirect_url' => $gatewayResult['redirect_url']];
            }
        }

        $session = $this->checkoutValidator->validatedCheckoutSession($request, $token);
        if ($session->payment_method_code !== self::METHOD_CODE) {
            throw new DomainException(__('storefront.payment_online_not_selected'));
        }

        $order = $this->orderCreationService->createFromCheckoutSession($request, $token);
        $transaction = $this->createTransaction($order);
        $method = $this->method();

        try {
            $gatewayResult = $this->gatewayManager->gateway($method->gateway_code)->createPayment($transaction, $method);
            $transaction->forceFill(['request_payload' => $gatewayResult['request_payload']])->save();
        } catch (Throwable) {
            $this->transition($transaction, [
                'valid' => true, 'status' => 'failed', 'transaction_number' => $transaction->transaction_number,
                'gateway_transaction_id' => null, 'amount' => (float) $transaction->amount,
                'currency_code' => $transaction->currency_code, 'event_id' => null, 'payload' => [],
                'error' => __('storefront.payment_gateway_error'),
            ]);
            throw new DomainException(__('storefront.payment_gateway_error'));
        }

        return ['order' => $order, 'transaction' => $transaction->refresh(), 'redirect_url' => $gatewayResult['redirect_url']];
    }

    /** @return array{transaction:PaymentTransaction,redirect_url:string} */
    public function retry(Request $request, Order $order): array
    {
        $this->assertOrderOwnership($request, $order);
        if ($order->payment_status === 'paid' || in_array($order->order_status, ['cancelled', 'completed'], true)) {
            throw new DomainException(__('storefront.payment_retry_not_allowed'));
        }

        $last = $order->paymentTransactions()->latest()->first();
        if ($last && in_array($last->status, ['pending', 'processing'], true) && $last->expired_at?->isPast()) {
            DB::transaction(function () use ($last, $order): void {
                $last->forceFill(['status' => 'expired', 'failure_reason' => 'Payment expired.'])->save();
                $order->forceFill(['payment_status' => 'failed'])->save();
                $order->orderPayments()->update(['payment_status' => 'failed']);
                $order->payment()->update(['status' => 'failed']);
            });
            $last->refresh();
        }
        if ($last && ! in_array($last->status, ['failed', 'cancelled', 'expired'], true)) {
            throw new DomainException(__('storefront.payment_retry_not_allowed'));
        }

        $transaction = $this->createTransaction($order);
        $method = $this->method();
        $result = $this->gatewayManager->gateway($method->gateway_code)->createPayment($transaction, $method);
        $transaction->forceFill(['request_payload' => $result['request_payload']])->save();

        return ['transaction' => $transaction->refresh(), 'redirect_url' => $result['redirect_url']];
    }

    public function processReturn(string $gatewayCode, array $payload): PaymentTransaction
    {
        $method = $this->method();
        abort_unless($method->gateway_code === $gatewayCode, 404);
        $result = $this->gatewayManager->gateway($gatewayCode)->verifyReturn($payload, $method);

        return $this->transitionFromResult($result);
    }

    public function processWebhook(string $gatewayCode, array $payload, array $headers): PaymentWebhookLog
    {
        $method = $this->method();
        abort_unless($method->gateway_code === $gatewayCode, 404);
        $result = $this->gatewayManager->gateway($gatewayCode)->verifyWebhook($payload, $method);
        $eventId = $result['event_id'] ?: hash('sha256', json_encode($result['payload']));
        $log = PaymentWebhookLog::query()->firstOrCreate([
            'gateway_code' => $gatewayCode,
            'event_id' => $eventId,
        ], [
            'event_type' => 'payment.'.$result['status'],
            'payload' => $result['payload'],
            'headers' => $this->safeHeaders($headers),
            'signature_valid' => $result['valid'],
        ]);
        if ($log->processed) {
            return $log;
        }

        $log->forceFill([
            'event_type' => 'payment.'.$result['status'],
            'payload' => $result['payload'],
            'headers' => $this->safeHeaders($headers),
            'signature_valid' => $result['valid'],
            'processing_error' => null,
        ])->save();

        try {
            $transaction = $this->transitionFromResult($result, true);
            $log->forceFill([
                'payment_transaction_id' => $transaction->id,
                'order_id' => $transaction->order_id,
                'processed' => true,
                'processed_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            // Persist an operational category only. Exception messages may
            // contain gateway payload, SQL bindings, or other sensitive data.
            $log->forceFill(['processing_error' => class_basename($exception)])->save();
            throw $exception;
        }

        return $log->refresh();
    }

    public function assertOrderOwnership(Request $request, Order $order): void
    {
        if ($order->user_id) {
            if ($request->user()?->id !== $order->user_id) {
                abort(403);
            }

            return;
        }

        $checkoutSessionId = $order->checkoutSession?->session_id;
        if (! $checkoutSessionId || ! hash_equals($checkoutSessionId, (string) $request->session()->get('cart_session_id'))) {
            abort(403);
        }
    }

    private function assertSelectable(Request $request, CheckoutSession $session, PaymentMethod $method): void
    {
        if (! $method->isUsable()) {
            throw new DomainException(__('storefront.payment_online_disabled'));
        }

        $summary = $this->checkoutValidator->checkoutSummaryForSession($request, $session);
        $this->checkoutValidator->assertCheckoutSnapshotStillMatches($session, $summary);
        $amount = (float) $session->grand_total;
        if ($amount <= 0) {
            throw new DomainException(__('storefront.payment_online_unavailable'));
        }
        if ($method->min_order_amount !== null && $amount < (float) $method->min_order_amount) {
            throw new DomainException(__('storefront.payment_online_unavailable'));
        }
        if ($method->max_order_amount !== null && $amount > (float) $method->max_order_amount) {
            throw new DomainException(__('storefront.payment_online_unavailable'));
        }
    }

    private function createTransaction(Order $order): PaymentTransaction
    {
        return DB::transaction(function () use ($order): PaymentTransaction {
            $locked = Order::query()->with('orderPayments')->lockForUpdate()->findOrFail($order->id);
            if ($locked->payment_status === 'paid' || $locked->order_status === 'cancelled') {
                throw new DomainException(__('storefront.payment_retry_not_allowed'));
            }
            $method = PaymentMethod::query()->where('code', self::METHOD_CODE)->active()->first();
            if (! $method?->isUsable()) {
                throw new DomainException(__('storefront.payment_online_disabled'));
            }
            $existing = $locked->paymentTransactions()->whereIn('status', ['pending', 'processing'])->latest()->first();
            if ($existing) {
                return $existing;
            }
            $orderPayment = $locked->orderPayments()->latest()->first();
            $transaction = $locked->paymentTransactions()->create([
                'order_payment_id' => $orderPayment?->id,
                'checkout_session_id' => $locked->checkout_session_id,
                'user_id' => $locked->user_id,
                'transaction_number' => 'PAY-'.now()->format('YmdHis').'-'.Str::upper(Str::random(12)),
                'gateway_code' => $method->gateway_code,
                'payment_method_code' => self::METHOD_CODE,
                'gateway_reference' => $locked->order_code,
                'status' => 'pending',
                'amount' => $locked->total_amount,
                'currency_code' => $locked->currency_code,
                'expired_at' => now()->addMinutes(30),
            ]);
            $locked->forceFill(['payment_status' => 'pending'])->save();
            $orderPayment?->forceFill(['payment_status' => 'pending'])->save();
            $locked->payment?->forceFill(['status' => 'pending'])->save();

            return $transaction;
        });
    }

    private function transitionFromResult(array $result, bool $webhook = false): PaymentTransaction
    {
        if (! $result['valid']) {
            throw new DomainException(__('storefront.payment_verification_failed'));
        }
        $transaction = PaymentTransaction::query()->where('transaction_number', $result['transaction_number'])->first();
        if (! $transaction) {
            throw new DomainException(__('storefront.payment_transaction_not_found'));
        }
        if ($result['amount'] === null || round($result['amount'], 2) !== round((float) $transaction->amount, 2)) {
            throw new DomainException(__('storefront.payment_amount_mismatch'));
        }
        if ($result['currency_code'] !== $transaction->currency_code) {
            throw new DomainException(__('storefront.payment_currency_mismatch'));
        }
        if ($webhook) {
            $result['webhook'] = true;
        }

        return $this->transition($transaction, $result);
    }

    private function transition(PaymentTransaction $transaction, array $result): PaymentTransaction
    {
        return DB::transaction(function () use ($transaction, $result): PaymentTransaction {
            $locked = PaymentTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            $order = Order::query()->lockForUpdate()->findOrFail($locked->order_id);
            if ($locked->status === 'paid' || $order->payment_status === 'paid') {
                return $locked;
            }

            $status = $result['status'];
            if ($order->order_status === 'cancelled' && $status === 'paid') {
                throw new DomainException(__('storefront.payment_order_cancelled'));
            }
            if ($locked->expired_at?->isPast() && $status !== 'paid') {
                $status = 'expired';
            }
            if ($locked->status === $status) {
                return $locked;
            }
            $fromStatus = $order->payment_status;
            $payloadField = ($result['webhook'] ?? false) ? 'webhook_payload' : 'response_payload';
            $locked->forceFill([
                'status' => $status,
                'gateway_transaction_id' => $result['gateway_transaction_id'],
                $payloadField => $result['payload'],
                'failure_reason' => in_array($status, ['failed', 'cancelled', 'expired'], true) ? ($result['error'] ?? $status) : null,
                'paid_at' => $status === 'paid' ? now() : null,
            ])->save();

            $orderPaymentStatus = $status === 'expired' ? 'failed' : $status;
            $order->forceFill([
                'payment_status' => $orderPaymentStatus,
                'paid_at' => $status === 'paid' ? now() : null,
            ])->save();
            $order->orderPayments()->update([
                'payment_status' => $orderPaymentStatus,
                'transaction_id' => $result['gateway_transaction_id'],
                'gateway_response' => $result['payload'],
                'paid_at' => $status === 'paid' ? now() : null,
            ]);
            $order->payment()->update([
                'status' => $orderPaymentStatus,
                'transaction_id' => $result['gateway_transaction_id'],
                'paid_at' => $status === 'paid' ? now() : null,
                'raw_response' => $result['payload'],
            ]);
            $order->paymentHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => $orderPaymentStatus,
                'note' => 'Verified '.$locked->gateway_code.' payment response.',
                'changed_by' => null,
            ]);

            return $locked->refresh();
        });
    }

    private function safeHeaders(array $headers): array
    {
        return collect($headers)
            ->except(['authorization', 'cookie', 'x-api-key'])
            ->map(fn ($value) => is_array($value) ? array_slice($value, 0, 3) : $value)
            ->all();
    }
}
