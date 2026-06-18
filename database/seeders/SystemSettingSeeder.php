<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::query()->upsert([
            ['key' => 'site_name', 'value' => 'E-commerce System', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'default_language', 'value' => 'vi', 'type' => 'string', 'group' => 'localization', 'is_public' => true],
            ['key' => 'default_currency', 'value' => 'VND', 'type' => 'string', 'group' => 'localization', 'is_public' => true],
            ['key' => 'tax_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'tax', 'is_public' => true],
            ['key' => 'price_include_tax', 'value' => 'false', 'type' => 'boolean', 'group' => 'tax', 'is_public' => true],
            ['key' => 'multi_language_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'localization', 'is_public' => true],
            ['key' => 'multi_currency_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'localization', 'is_public' => true],
        ], ['key'], ['value', 'type', 'group', 'is_public']);
    }
}
