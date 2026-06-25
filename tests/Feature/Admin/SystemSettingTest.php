<?php

namespace Tests\Feature\Admin;

use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SystemSettingService;
use Database\Seeders\SystemSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SystemSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SystemSettingSeeder::class);
    }

    public function test_admin_can_view_system_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('General Settings')
            ->assertSee('Localization Settings')
            ->assertSee('Tax Settings')
            ->assertSee('Order Settings')
            ->assertSee('Save Settings');
    }

    public function test_guest_and_customer_cannot_access_system_settings(): void
    {
        $this->get('/admin/settings')->assertRedirect(route('login'));

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get('/admin/settings')->assertForbidden();
    }

    public function test_admin_can_update_only_whitelisted_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put('/admin/settings', $this->validPayload([
                'site_name' => 'New Store Name',
                'site_email' => 'hello@example.com',
                'unexpected_secret' => 'must-not-be-saved',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect()
            ->assertSessionHas('success', 'Settings updated successfully.');

        $this->assertDatabaseHas('system_settings', [
            'key' => 'site_name',
            'value' => 'New Store Name',
            'type' => 'string',
            'group' => 'general',
        ]);
        $this->assertDatabaseMissing('system_settings', ['key' => 'unexpected_secret']);
        $this->assertTrue(Cache::missing(SystemSettingService::CACHE_KEY));
    }

    public function test_unchecked_boolean_settings_are_stored_as_false(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = $this->validPayload();
        unset(
            $payload['multi_language_enabled'],
            $payload['multi_currency_enabled'],
            $payload['tax_enabled'],
            $payload['price_include_tax'],
        );

        $this->actingAs($admin)
            ->put('/admin/settings', $payload)
            ->assertSessionHasNoErrors();

        foreach (['multi_language_enabled', 'multi_currency_enabled', 'tax_enabled', 'price_include_tax'] as $key) {
            $this->assertDatabaseHas('system_settings', ['key' => $key, 'value' => '0']);
        }

        $service = app(SystemSettingService::class);
        $this->assertFalse($service->get('multi_language_enabled'));
        $this->assertFalse($service->get('multi_currency_enabled'));
        $this->assertFalse($service->get('tax_enabled'));
        $this->assertFalse($service->get('price_include_tax'));
    }

    public function test_settings_validation_rejects_invalid_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from('/admin/settings')
            ->put('/admin/settings', $this->validPayload([
                'site_name' => '',
                'site_email' => 'not-an-email',
                'default_language' => 'xx',
                'default_shipping_fee' => -1,
            ]))
            ->assertRedirect('/admin/settings')
            ->assertSessionHasErrors(['site_name', 'site_email', 'default_language', 'default_shipping_fee']);
    }

    public function test_service_casts_groups_and_clears_cache(): void
    {
        $service = app(SystemSettingService::class);

        $this->assertTrue($service->get('tax_enabled'));
        $this->assertSame(30000, $service->get('default_shipping_fee'));
        $this->assertArrayHasKey('site_name', $service->getGroup('general'));
        $this->assertArrayHasKey('default_currency', $service->all());
        $this->assertTrue(Cache::has(SystemSettingService::CACHE_KEY));

        SystemSetting::query()->where('key', 'site_name')->update(['value' => 'Changed directly']);
        $this->assertSame('E-commerce System', $service->get('site_name'));

        $service->clearCache();
        $this->assertSame('Changed directly', $service->get('site_name'));
    }

    public function test_system_setting_seeder_is_idempotent(): void
    {
        $this->seed(SystemSettingSeeder::class);

        $this->assertSame(count(SystemSettingService::DEFINITIONS), SystemSetting::query()->count());
    }

    private function validPayload(array $overrides = []): array
    {
        return array_replace([
            'site_name' => 'E-commerce System',
            'site_email' => 'support@example.com',
            'site_phone' => '0901234567',
            'site_address' => 'Ho Chi Minh City',
            'site_logo' => '/storage/branding/logo.svg',
            'site_favicon' => '/storage/branding/favicon.ico',
            'default_language' => 'vi',
            'default_currency' => 'VND',
            'multi_language_enabled' => '1',
            'multi_currency_enabled' => '1',
            'tax_enabled' => '1',
            'price_include_tax' => '0',
            'default_shipping_fee' => '30000',
            'free_shipping_min_amount' => '500000',
            'order_code_prefix' => 'ORD',
            'payment_cod_enabled' => '1',
            'payment_cod_display_name' => 'Cash on Delivery',
            'payment_cod_description' => 'Pay with cash when your order is delivered.',
            'payment_cod_instruction' => 'Please prepare the exact amount when receiving your order.',
            'payment_cod_min_order_amount' => null,
            'payment_cod_max_order_amount' => null,
            'payment_cod_sort_order' => '10',
        ], $overrides);
    }
}
