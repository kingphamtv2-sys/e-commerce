<?php

namespace Tests\Feature\Admin;

use App\Jobs\SendEmailNotification;
use App\Mail\TransactionalMail;
use App\Models\EmailLog;
use App\Models\Language;
use App\Models\Order;
use App\Models\User;
use App\Services\EmailNotificationService;
use App\Services\SystemSettingService;
use Database\Seeders\SystemSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SystemSettingSeeder::class);
        Language::query()->create([
            'code' => 'vi',
            'name' => 'Vietnamese',
            'native_name' => 'Tiếng Việt',
            'is_default' => true,
            'status' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_email_settings_routes_require_admin_and_test_route_is_rate_limited(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $this->get(route('admin.settings.email.edit'))->assertRedirect(route('login'));
        $this->actingAs($customer)->get(route('admin.settings.email.edit'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.settings.email.edit'))
            ->assertOk()
            ->assertDontSee('MAIL_PASSWORD')
            ->assertDontSee('test-signing-secret');

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->actingAs($admin)->post(route('admin.settings.email.test'), [
                'recipient_email' => 'admin@example.com',
            ])->assertRedirect();
        }

        $this->actingAs($admin)->post(route('admin.settings.email.test'), [
            'recipient_email' => 'admin@example.com',
        ])->assertTooManyRequests();
    }

    public function test_admin_can_update_notification_settings_without_storing_secrets(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->patch(route('admin.settings.email.update'), [
            'email_notifications_enabled' => '1',
            'admin_order_email_enabled' => '1',
            'customer_order_email_enabled' => '1',
            'payment_email_enabled' => '1',
            'payment_failed_email_enabled' => '1',
            'order_status_email_enabled' => '1',
            'admin_notification_emails' => 'Owner@Example.com, ops@example.com',
            'email_from_name' => 'Shop Orders',
            'email_from_address' => 'orders@example.com',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'admin_notification_emails',
            'value' => 'owner@example.com,ops@example.com',
            'is_public' => false,
        ]);
        $this->assertDatabaseMissing('system_settings', ['key' => 'mail_password']);
    }

    public function test_order_and_payment_events_are_idempotent_and_use_snapshot_recipient_locale(): void
    {
        Queue::fake();
        $this->setSetting('admin_notification_emails', 'ops@example.com');
        $this->setSetting('payment_failed_email_enabled', '1');
        $order = $this->order(['language_code' => 'vi', 'customer_email' => 'snapshot@example.com']);
        $transaction = $order->paymentTransactions()->create([
            'transaction_number' => 'PAY-EMAIL-1',
            'gateway_code' => 'mock',
            'payment_method_code' => 'online',
            'status' => 'paid',
            'amount' => 125000,
            'currency_code' => 'VND',
            'paid_at' => now(),
        ]);
        $failedTransaction = $order->paymentTransactions()->create([
            'transaction_number' => 'PAY-EMAIL-2',
            'gateway_code' => 'mock',
            'payment_method_code' => 'online',
            'status' => 'failed',
            'amount' => 125000,
            'currency_code' => 'VND',
        ]);
        $service = app(EmailNotificationService::class);

        $service->orderCreated($order);
        $service->orderCreated($order);
        $service->paymentChanged($order, $transaction, 'paid');
        $service->paymentChanged($order, $transaction, 'paid');
        $service->paymentChanged($order, $failedTransaction, 'failed');
        $service->orderStatusUpdated($order, 'pending', 'confirmed', 99);
        $service->orderCancelled($order);

        $this->assertDatabaseCount('email_logs', 6);
        $this->assertDatabaseHas('email_logs', [
            'event' => EmailNotificationService::ORDER_CREATED,
            'recipient_email' => 'snapshot@example.com',
            'locale' => 'vi',
        ]);
        $this->assertDatabaseHas('email_logs', [
            'event' => EmailNotificationService::ADMIN_NEW_ORDER,
            'recipient_email' => 'ops@example.com',
        ]);
        $this->assertDatabaseHas('email_logs', [
            'event' => EmailNotificationService::PAYMENT_SUCCESS,
            'payment_transaction_id' => $transaction->id,
        ]);
        $this->assertDatabaseHas('email_logs', [
            'event' => EmailNotificationService::PAYMENT_FAILED,
            'payment_transaction_id' => $failedTransaction->id,
        ]);
        $this->assertDatabaseHas('email_logs', [
            'event' => EmailNotificationService::ORDER_STATUS_UPDATED,
            'order_id' => $order->id,
        ]);
        $this->assertDatabaseHas('email_logs', [
            'event' => EmailNotificationService::ORDER_CANCELLED,
            'order_id' => $order->id,
        ]);
        Queue::assertPushed(SendEmailNotification::class, 6);
    }

    public function test_blade_email_renders_order_and_currency_snapshots(): void
    {
        $order = $this->order();
        $order->orderItems()->create([
            'product_name' => 'Snapshot Product',
            'sku' => 'SNAP-01',
            'price' => 125000,
            'quantity' => 1,
            'subtotal' => 125000,
            'taxable_amount' => 125000,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total' => 125000,
        ]);
        $log = EmailLog::query()->create([
            'event' => EmailNotificationService::ORDER_CREATED,
            'idempotency_key' => hash('sha256', 'render-test'),
            'order_id' => $order->id,
            'recipient_email' => $order->customer_email,
            'subject' => 'Order snapshot',
            'locale' => 'vi',
            'status' => 'pending',
        ]);

        app()->setLocale('vi');
        $html = (new TransactionalMail($log->load('order')))->render();

        $this->assertStringContainsString('Snapshot Product', $html);
        $this->assertStringContainsString('SNAP-01', $html);
        $this->assertStringContainsString('125.000 ₫', $html);
        $this->assertStringNotContainsString('MAIL_PASSWORD', $html);
    }

    private function setSetting(string $key, string $value): void
    {
        app(SystemSettingService::class)->set(
            $key,
            $value,
            SystemSettingService::DEFINITIONS[$key]['type'],
            SystemSettingService::DEFINITIONS[$key]['group'],
            false,
        );
    }

    private function order(array $overrides = []): Order
    {
        return Order::query()->create(array_merge([
            'order_code' => 'ORD-EMAIL-'.fake()->unique()->numerify('####'),
            'success_token' => fake()->unique()->sha1(),
            'customer_name' => 'Snapshot Buyer',
            'customer_phone' => '0900000000',
            'customer_email' => 'buyer@example.com',
            'language_code' => 'vi',
            'shipping_address' => '1 Nguyen Hue',
            'currency_code' => 'VND',
            'currency_symbol' => '₫',
            'currency_symbol_position' => 'after',
            'currency_decimal_places' => 0,
            'currency_snapshot' => [
                'code' => 'VND',
                'symbol' => '₫',
                'symbol_position' => 'after',
                'decimal_places' => 0,
                'decimal_separator' => ',',
                'thousand_separator' => '.',
            ],
            'exchange_rate' => 1,
            'subtotal' => 125000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'shipping_fee' => 0,
            'total_amount' => 125000,
            'payment_method' => 'online',
            'payment_method_name' => 'Mock Pay',
            'payment_status' => 'paid',
            'order_status' => 'pending',
            'fulfillment_status' => 'unfulfilled',
            'placed_at' => now(),
        ], $overrides));
    }
}
