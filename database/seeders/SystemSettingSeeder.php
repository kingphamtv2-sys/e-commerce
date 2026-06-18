<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Services\SystemSettingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::query()->upsert([
            ['key' => 'site_name', 'value' => 'E-commerce System', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'site_email', 'value' => null, 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'site_phone', 'value' => null, 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'site_address', 'value' => null, 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'site_logo', 'value' => null, 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'site_favicon', 'value' => null, 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'default_language', 'value' => 'vi', 'type' => 'string', 'group' => 'localization', 'is_public' => true],
            ['key' => 'default_currency', 'value' => 'VND', 'type' => 'string', 'group' => 'localization', 'is_public' => true],
            ['key' => 'multi_language_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'localization', 'is_public' => true],
            ['key' => 'multi_currency_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'localization', 'is_public' => true],
            ['key' => 'tax_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'tax', 'is_public' => true],
            ['key' => 'price_include_tax', 'value' => '0', 'type' => 'boolean', 'group' => 'tax', 'is_public' => true],
            ['key' => 'default_shipping_fee', 'value' => '30000', 'type' => 'number', 'group' => 'order', 'is_public' => true],
            ['key' => 'free_shipping_min_amount', 'value' => '500000', 'type' => 'number', 'group' => 'order', 'is_public' => true],
            ['key' => 'order_code_prefix', 'value' => 'ORD', 'type' => 'string', 'group' => 'order', 'is_public' => false],
        ], ['key'], ['value', 'type', 'group', 'is_public']);

        Cache::forget(SystemSettingService::CACHE_KEY);
    }
}
