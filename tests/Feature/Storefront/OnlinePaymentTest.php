<?php

namespace Tests\Feature\Storefront;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\PaymentWebhookLog;
use App\Models\User;
use App\Payments\Gateways\MockPaymentGateway;
use App\Services\OnlinePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlinePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_configure_online_payment_without_exposing_saved_secret(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->patch(route('admin.settings.payment.online.update'), [
            'enabled' => '1',
            'name' => 'Sandbox Pay',
            'description' => 'Redirect payment',
            'instruction' => 'Continue to gateway',
            'gateway_code' => 'mock',
            'environment' => 'sandbox',
            'secret_key' => 'test-signing-secret-123456',
            'sort_order' => 20,
        ])->assertRedirect();

        $method = PaymentMethod::query()->where('code', 'online')->firstOrFail();
        $this->assertSame('active', $method->status);
        $this->assertSame('test-signing-secret-123456', $method->credentials['secret_key']);
        $this->assertDatabaseMissing('payment_methods', ['credentials' => 'test-signing-secret-123456']);

        $this->actingAs($admin)->get(route('admin.settings.payment.online.edit'))
            ->assertOk()
            ->assertDontSee('test-signing-secret-123456')
            ->assertSee(__('admin.online_payment.secret_saved'));
    }

    public function test_valid_return_marks_transaction_and_order_paid_idempotently(): void
    {
        [$order, $transaction, $method] = $this->payment();
        $gateway = app(MockPaymentGateway::class);
        $payload = $gateway->signedResult($transaction, $method, 'paid');
        $service = app(OnlinePaymentService::class);

        $service->processReturn('mock', $payload);
        $service->processReturn('mock', $payload);

        $this->assertSame('paid', $transaction->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertNotNull($transaction->fresh()->paid_at);
        $this->assertNotNull($order->orderPayments()->first()->paid_at);
        $this->assertSame(1, $order->paymentHistories()->count());
    }

    public function test_invalid_signature_amount_or_currency_never_marks_order_paid(): void
    {
        foreach (['signature', 'amount', 'currency'] as $case) {
            [$order, $transaction, $method] = $this->payment();
            $payload = app(MockPaymentGateway::class)->signedResult($transaction, $method, 'paid');

            if ($case === 'signature') {
                $payload['signature'] = 'invalid';
            } elseif ($case === 'amount') {
                $payload['amount'] = '1.00';
                $payload = $this->resign($transaction, $method, $payload);
            } else {
                $payload['currency_code'] = 'USD';
                $payload = $this->resign($transaction, $method, $payload);
            }

            try {
                app(OnlinePaymentService::class)->processReturn('mock', $payload);
            } catch (\DomainException) {
                // Expected verification rejection.
            }

            $this->assertNotSame('paid', $order->fresh()->payment_status);
            $this->assertNotSame('paid', $transaction->fresh()->status);
        }
    }

    public function test_webhook_is_verified_logged_and_duplicate_event_is_not_reprocessed(): void
    {
        [$order, $transaction, $method] = $this->payment();
        $payload = app(MockPaymentGateway::class)->signedResult($transaction, $method, 'paid');

        $first = $this->postJson(route('payment.webhook', 'mock'), $payload);
        $second = $this->postJson(route('payment.webhook', 'mock'), $payload);

        $first->assertOk()->assertJsonPath('processed', true);
        $second->assertOk()->assertJsonPath('processed', true);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertDatabaseCount('payment_webhook_logs', 1);
        $this->assertSame(1, $order->paymentHistories()->count());
        $this->assertArrayNotHasKey('signature', $order->paymentTransactions()->firstOrFail()->webhook_payload);
        $this->assertArrayNotHasKey('signature', PaymentWebhookLog::query()->firstOrFail()->payload);
    }

    public function test_gateway_rejects_signatures_when_no_secret_is_configured(): void
    {
        [$order, $transaction, $method] = $this->payment();
        $payload = app(MockPaymentGateway::class)->signedResult($transaction, $method, 'paid');
        $method->update(['credentials' => []]);

        try {
            app(OnlinePaymentService::class)->processReturn('mock', $payload);
        } catch (\DomainException) {
            // Expected verification rejection.
        }

        $this->assertNotSame('paid', $order->fresh()->payment_status);
        $this->assertNotSame('paid', $transaction->fresh()->status);
    }

    public function test_retry_creates_new_attempt_only_for_retryable_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        [$order, $transaction] = $this->payment($customer);
        $transaction->update(['status' => 'failed']);
        $order->update(['payment_status' => 'failed']);

        $this->actingAs($customer)->post(route('orders.payment.retry', $order))
            ->assertRedirect();
        $this->assertSame(2, $order->paymentTransactions()->count());

        $order->update(['payment_status' => 'paid']);
        $this->actingAs($customer)->post(route('orders.payment.retry', $order))
            ->assertSessionHasErrors('payment');
        $this->assertSame(2, $order->paymentTransactions()->count());
    }

    public function test_admin_order_detail_displays_payment_attempts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$order, $transaction] = $this->payment();

        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee(__('admin.orders.payment_transactions'))
            ->assertSee($transaction->transaction_number);
    }

    private function payment(?User $user = null): array
    {
        $method = PaymentMethod::query()->updateOrCreate(['code' => 'online'], [
            'name' => 'Mock Pay',
            'gateway_code' => 'mock',
            'environment' => 'sandbox',
            'credentials' => ['secret_key' => 'test-signing-secret-123456'],
            'sort_order' => 20,
            'status' => 'active',
        ]);
        $order = Order::query()->create([
            'user_id' => $user?->id,
            'order_code' => 'ORD-'.fake()->unique()->numerify('######'),
            'success_token' => fake()->unique()->sha1(),
            'customer_name' => 'Online Buyer',
            'customer_phone' => '0900000000',
            'customer_email' => 'online@example.com',
            'shipping_address' => 'Hanoi',
            'currency_code' => 'VND',
            'exchange_rate' => 1,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'shipping_fee' => 0,
            'total_amount' => 100000,
            'payment_method' => 'online',
            'payment_method_name' => 'Mock Pay',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'fulfillment_status' => 'unfulfilled',
            'placed_at' => now(),
        ]);
        $payment = $order->orderPayments()->create([
            'payment_method_code' => 'online',
            'payment_method_name' => 'Mock Pay',
            'payment_status' => 'pending',
            'amount' => 100000,
            'currency_code' => 'VND',
        ]);
        $order->payment()->create([
            'payment_method' => 'online',
            'amount' => 100000,
            'currency_code' => 'VND',
            'status' => 'pending',
        ]);
        $transaction = $order->paymentTransactions()->create([
            'order_payment_id' => $payment->id,
            'user_id' => $user?->id,
            'transaction_number' => 'PAY-'.fake()->unique()->numerify('########'),
            'gateway_code' => 'mock',
            'payment_method_code' => 'online',
            'gateway_reference' => $order->order_code,
            'status' => 'pending',
            'amount' => 100000,
            'currency_code' => 'VND',
            'expired_at' => now()->addMinutes(30),
        ]);

        return [$order, $transaction, $method];
    }

    private function resign(PaymentTransaction $transaction, PaymentMethod $method, array $payload): array
    {
        unset($payload['signature']);
        ksort($payload);
        $payload['signature'] = hash_hmac('sha256', http_build_query($payload), $method->credentials['secret_key']);

        return $payload;
    }
}
