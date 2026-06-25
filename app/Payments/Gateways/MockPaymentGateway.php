<?php

namespace App\Payments\Gateways;

use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Payments\Contracts\PaymentGatewayInterface;

class MockPaymentGateway implements PaymentGatewayInterface
{
    public function code(): string
    {
        return 'mock';
    }

    public function createPayment(PaymentTransaction $transaction, PaymentMethod $method): array
    {
        $payload = [
            'transaction_number' => $transaction->transaction_number,
            'amount' => number_format((float) $transaction->amount, 2, '.', ''),
            'currency_code' => $transaction->currency_code,
            'expires_at' => $transaction->expired_at?->timestamp,
        ];
        $payload['signature'] = $this->sign($payload, $method);

        return [
            'redirect_url' => route('payment.mock.show', $payload),
            'request_payload' => $payload,
        ];
    }

    public function verifyReturn(array $payload, PaymentMethod $method): array
    {
        return $this->verify($payload, $method);
    }

    public function verifyWebhook(array $payload, PaymentMethod $method): array
    {
        return $this->verify($payload, $method);
    }

    public function signedResult(PaymentTransaction $transaction, PaymentMethod $method, string $status): array
    {
        $payload = [
            'transaction_number' => $transaction->transaction_number,
            'gateway_transaction_id' => 'MOCK-'.$transaction->id,
            'amount' => number_format((float) $transaction->amount, 2, '.', ''),
            'currency_code' => $transaction->currency_code,
            'status' => $status,
            'event_id' => 'mock-'.$transaction->id.'-'.$status,
        ];
        $payload['signature'] = $this->sign($payload, $method);

        return $payload;
    }

    private function verify(array $payload, PaymentMethod $method): array
    {
        $signature = (string) ($payload['signature'] ?? '');
        $secret = (string) ($method->credentials['secret_key'] ?? '');
        $unsigned = $payload;
        unset($unsigned['signature']);
        $valid = $secret !== ''
            && $signature !== ''
            && hash_equals($this->sign($unsigned, $method), $signature);
        $status = in_array($payload['status'] ?? null, ['paid', 'pending', 'failed', 'cancelled', 'expired'], true)
            ? $payload['status']
            : 'failed';

        return [
            'valid' => $valid,
            'status' => $status,
            'transaction_number' => $payload['transaction_number'] ?? null,
            'gateway_transaction_id' => $payload['gateway_transaction_id'] ?? null,
            'amount' => isset($payload['amount']) ? (float) $payload['amount'] : null,
            'currency_code' => $payload['currency_code'] ?? null,
            'event_id' => $payload['event_id'] ?? null,
            'payload' => $this->safePayload($payload),
            'error' => $valid ? null : 'Invalid signature.',
        ];
    }

    private function sign(array $payload, PaymentMethod $method): string
    {
        ksort($payload);

        return hash_hmac('sha256', http_build_query($payload), (string) ($method->credentials['secret_key'] ?? ''));
    }

    private function safePayload(array $payload): array
    {
        unset($payload['signature']);

        return $payload;
    }
}
