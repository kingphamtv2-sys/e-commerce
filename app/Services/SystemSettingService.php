<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingService
{
    public const CACHE_KEY = 'system_settings';

    public const DEFINITIONS = [
        'site_name' => ['type' => 'string', 'group' => 'general', 'public' => true, 'default' => 'E-commerce System'],
        'site_email' => ['type' => 'string', 'group' => 'general', 'public' => true, 'default' => null],
        'site_phone' => ['type' => 'string', 'group' => 'general', 'public' => true, 'default' => null],
        'site_address' => ['type' => 'string', 'group' => 'general', 'public' => true, 'default' => null],
        'site_logo' => ['type' => 'string', 'group' => 'general', 'public' => true, 'default' => null],
        'site_favicon' => ['type' => 'string', 'group' => 'general', 'public' => true, 'default' => null],
        'default_language' => ['type' => 'string', 'group' => 'localization', 'public' => true, 'default' => 'vi'],
        'default_currency' => ['type' => 'string', 'group' => 'localization', 'public' => true, 'default' => 'VND'],
        'multi_language_enabled' => ['type' => 'boolean', 'group' => 'localization', 'public' => true, 'default' => true],
        'multi_currency_enabled' => ['type' => 'boolean', 'group' => 'localization', 'public' => true, 'default' => true],
        'tax_enabled' => ['type' => 'boolean', 'group' => 'tax', 'public' => true, 'default' => true],
        'price_include_tax' => ['type' => 'boolean', 'group' => 'tax', 'public' => true, 'default' => false],
        'default_shipping_fee' => ['type' => 'number', 'group' => 'order', 'public' => true, 'default' => 30000],
        'free_shipping_min_amount' => ['type' => 'number', 'group' => 'order', 'public' => true, 'default' => 500000],
        'order_code_prefix' => ['type' => 'string', 'group' => 'order', 'public' => false, 'default' => 'ORD'],
        'payment_cod_enabled' => ['type' => 'boolean', 'group' => 'payment', 'public' => true, 'default' => true],
        'payment_cod_display_name' => ['type' => 'string', 'group' => 'payment', 'public' => true, 'default' => 'Cash on Delivery'],
        'payment_cod_description' => ['type' => 'string', 'group' => 'payment', 'public' => true, 'default' => 'Pay with cash when your order is delivered.'],
        'payment_cod_instruction' => ['type' => 'string', 'group' => 'payment', 'public' => true, 'default' => 'Please prepare the exact amount when receiving your order.'],
        'payment_cod_min_order_amount' => ['type' => 'number', 'group' => 'payment', 'public' => false, 'default' => null],
        'payment_cod_max_order_amount' => ['type' => 'number', 'group' => 'payment', 'public' => false, 'default' => null],
        'payment_cod_sort_order' => ['type' => 'number', 'group' => 'payment', 'public' => false, 'default' => 10],
        'email_notifications_enabled' => ['type' => 'boolean', 'group' => 'email', 'public' => false, 'default' => true],
        'admin_order_email_enabled' => ['type' => 'boolean', 'group' => 'email', 'public' => false, 'default' => true],
        'customer_order_email_enabled' => ['type' => 'boolean', 'group' => 'email', 'public' => false, 'default' => true],
        'payment_email_enabled' => ['type' => 'boolean', 'group' => 'email', 'public' => false, 'default' => true],
        'payment_failed_email_enabled' => ['type' => 'boolean', 'group' => 'email', 'public' => false, 'default' => false],
        'order_status_email_enabled' => ['type' => 'boolean', 'group' => 'email', 'public' => false, 'default' => true],
        'admin_notification_emails' => ['type' => 'string', 'group' => 'email', 'public' => false, 'default' => null],
        'email_from_name' => ['type' => 'string', 'group' => 'email', 'public' => false, 'default' => null],
        'email_from_address' => ['type' => 'string', 'group' => 'email', 'public' => false, 'default' => null],
    ];

    public function all(): array
    {
        return array_map(static fn (array $setting): mixed => $setting['value'], $this->settings());
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settings()[$key]['value'] ?? $default;
    }

    public function getGroup(string $group): array
    {
        return collect($this->settings())
            ->filter(static fn (array $setting): bool => $setting['group'] === $group)
            ->mapWithKeys(static fn (array $setting, string $key): array => [$key => $setting['value']])
            ->all();
    }

    public function set(string $key, mixed $value, string $type = 'string', ?string $group = null, bool $isPublic = false): void
    {
        SystemSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $this->serializeValue($value, $type),
                'type' => $type,
                'group' => $group,
                'is_public' => $isPublic,
            ],
        );

        $this->clearCache();
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function settings(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return SystemSetting::query()
                ->get(['key', 'value', 'type', 'group'])
                ->mapWithKeys(fn (SystemSetting $setting): array => [
                    $setting->key => [
                        'value' => $this->castValue($setting->value, $setting->type),
                        'group' => $setting->group,
                    ],
                ])
                ->all();
        });
    }

    private function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => str_contains($value, '.') ? (float) $value : (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    private function serializeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
            'json' => json_encode($value, JSON_THROW_ON_ERROR),
            default => (string) $value,
        };
    }
}
