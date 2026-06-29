<?php

namespace App\Services;

use App\Jobs\SendEmailNotification;
use App\Models\EmailLog;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class EmailNotificationService
{
    public const ORDER_CREATED = 'order_created';

    public const ADMIN_NEW_ORDER = 'admin_new_order';

    public const PAYMENT_SUCCESS = 'payment_success';

    public const PAYMENT_FAILED = 'payment_failed';

    public const ORDER_STATUS_UPDATED = 'order_status_updated';

    public const ORDER_CANCELLED = 'order_cancelled';

    public const TEST_EMAIL = 'test_email';

    public function __construct(
        private readonly SystemSettingService $settings,
        private readonly LanguageService $languages,
    ) {}

    public function orderCreated(Order $order): void
    {
        $this->safely(self::ORDER_CREATED, $order, function () use ($order): void {
            if (! $this->enabled()) {
                return;
            }

            if ($this->settings->get('customer_order_email_enabled', true)) {
                $this->reserve(
                    self::ORDER_CREATED,
                    $order->customer_email,
                    $this->orderLocale($order),
                    $order,
                    null,
                    'customer',
                );
            }

            if ($this->settings->get('admin_order_email_enabled', true)) {
                foreach ($this->adminRecipients() as $recipient) {
                    $this->reserve(
                        self::ADMIN_NEW_ORDER,
                        $recipient,
                        $this->defaultLocale(),
                        $order,
                        null,
                        'admin:'.$recipient,
                    );
                }
            }
        });
    }

    public function paymentChanged(Order $order, ?PaymentTransaction $transaction, string $status): void
    {
        $event = $status === 'paid' ? self::PAYMENT_SUCCESS : self::PAYMENT_FAILED;

        $this->safely($event, $order, function () use ($order, $transaction, $status): void {
            if (! $this->enabled() || ! $this->settings->get('payment_email_enabled', true)) {
                return;
            }

            if ($status === 'paid') {
                $this->reserve(
                    self::PAYMENT_SUCCESS,
                    $order->customer_email,
                    $this->orderLocale($order),
                    $order,
                    $transaction,
                    'paid',
                    ['transaction_number' => $transaction?->transaction_number],
                );

                return;
            }

            if (
                in_array($status, ['failed', 'cancelled', 'expired'], true)
                && $this->settings->get('payment_failed_email_enabled', false)
            ) {
                $this->reserve(
                    self::PAYMENT_FAILED,
                    $order->customer_email,
                    $this->orderLocale($order),
                    $order,
                    $transaction,
                    $status,
                    ['payment_status' => $status],
                );
            }
        });
    }

    public function orderStatusUpdated(Order $order, string $from, string $to, int|string $changeId): void
    {
        $this->safely(self::ORDER_STATUS_UPDATED, $order, function () use ($order, $from, $to, $changeId): void {
            if (! $this->enabled() || ! $this->settings->get('order_status_email_enabled', true)) {
                return;
            }

            if ($to === 'cancelled') {
                $this->reserveOrderCancelled($order);

                return;
            }

            $this->reserve(
                self::ORDER_STATUS_UPDATED,
                $order->customer_email,
                $this->orderLocale($order),
                $order,
                null,
                'status:'.$changeId,
                ['from_status' => $from, 'to_status' => $to],
            );
        });
    }

    public function orderCancelled(Order $order): void
    {
        $this->safely(self::ORDER_CANCELLED, $order, function () use ($order): void {
            if (! $this->enabled() || ! $this->settings->get('order_status_email_enabled', true)) {
                return;
            }

            $this->reserveOrderCancelled($order);
        });
    }

    private function reserveOrderCancelled(Order $order): void
    {
        $this->reserve(
            self::ORDER_CANCELLED,
            $order->customer_email,
            $this->orderLocale($order),
            $order,
            null,
            'cancelled',
        );
    }

    private function safely(string $event, Order $order, callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $exception) {
            Log::warning('Transactional email dispatch failed.', [
                'event' => $event,
                'order_id' => $order->id,
                'exception' => class_basename($exception),
            ]);
        }
    }

    public function testEmail(string $recipient): EmailLog
    {
        return $this->reserve(
            self::TEST_EMAIL,
            $recipient,
            $this->defaultLocale(),
            null,
            null,
            (string) Str::uuid(),
            ['tested_at' => now()->toIso8601String()],
            true,
        );
    }

    private function reserve(
        string $event,
        ?string $recipient,
        string $locale,
        ?Order $order,
        ?PaymentTransaction $transaction,
        string $context,
        array $payload = [],
        bool $force = false,
    ): EmailLog {
        $recipient = strtolower(trim((string) $recipient));
        $key = hash('sha256', implode('|', [
            $event,
            $order?->id ?: 0,
            $transaction?->id ?: 0,
            $recipient,
            $context,
        ]));
        $subject = Lang::get('emails.subjects.'.$event, [
            'order' => $order?->order_code,
            'store' => $this->settings->get('site_name', config('app.name')),
        ], $locale);

        $log = EmailLog::query()->firstOrCreate(
            ['idempotency_key' => $key],
            [
                'event' => $event,
                'order_id' => $order?->id,
                'payment_transaction_id' => $transaction?->id,
                'recipient_email' => $recipient,
                'subject' => $subject,
                'locale' => $locale,
                'status' => filter_var($recipient, FILTER_VALIDATE_EMAIL) ? 'pending' : 'skipped',
                'payload' => $payload,
                'error_message' => filter_var($recipient, FILTER_VALIDATE_EMAIL) ? null : 'InvalidRecipient',
            ],
        );

        if (
            $log->wasRecentlyCreated
            && $log->status === 'pending'
            && ($force || $this->enabled())
        ) {
            try {
                SendEmailNotification::dispatch($log->id);
            } catch (Throwable $exception) {
                $log->forceFill([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error_message' => class_basename($exception),
                ])->save();
            }
        }

        return $log->refresh();
    }

    private function enabled(): bool
    {
        return (bool) $this->settings->get('email_notifications_enabled', true);
    }

    private function adminRecipients(): array
    {
        $configured = (string) $this->settings->get('admin_notification_emails', '');
        $fallback = (string) $this->settings->get('site_email', '');

        return collect(explode(',', $configured ?: $fallback))
            ->map(fn (string $email): string => strtolower(trim($email)))
            ->filter(fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }

    private function orderLocale(Order $order): string
    {
        $candidate = strtolower((string) $order->language_code);

        return $this->supportedLocale($candidate) ?: $this->defaultLocale();
    }

    private function defaultLocale(): string
    {
        $candidate = $this->languages->getDefault()?->code
            ?: $this->settings->get('default_language', config('app.locale'));

        return $this->supportedLocale((string) $candidate)
            ?: $this->supportedLocale((string) config('app.fallback_locale'))
            ?: 'en';
    }

    private function supportedLocale(string $locale): ?string
    {
        $locale = strtolower($locale);

        return is_file(lang_path($locale.'/emails.php')) ? $locale : null;
    }
}
