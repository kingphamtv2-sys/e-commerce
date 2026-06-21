<?php

namespace Tests\Feature\Admin;

use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Models\User;
use App\Services\SystemSettingService;
use App\Services\TaxService;
use Database\Seeders\SystemSettingSeeder;
use Database\Seeders\TaxClassSeeder;
use Database\Seeders\TaxRateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TaxManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SystemSettingSeeder::class, TaxClassSeeder::class, TaxRateSeeder::class]);
    }

    public function test_admin_can_view_tax_management_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/tax-classes')->assertOk()->assertSee('Standard Tax');
        $this->actingAs($admin)->get('/admin/tax-rates')->assertOk()->assertSee('10%');
        $this->actingAs($admin)->get('/admin/tax-classes/create')->assertOk();
        $this->actingAs($admin)->get('/admin/tax-rates/create')->assertOk();
    }

    public function test_guest_and_customer_cannot_access_tax_management(): void
    {
        $this->get('/admin/tax-classes')->assertRedirect(route('login'));
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get('/admin/tax-rates')->assertForbidden();
    }

    public function test_admin_can_create_and_update_tax_class_with_normalized_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Cache::put(TaxService::CACHE_CLASSES, ['stale']);

        $this->actingAs($admin)->post('/admin/tax-classes', [
            'code' => ' Luxury_Tax ', 'name' => 'Luxury Tax', 'description' => 'Luxury goods', 'status' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.tax-classes.index'));

        $taxClass = TaxClass::query()->where('code', 'luxury_tax')->firstOrFail();
        $this->assertTrue(Cache::missing(TaxService::CACHE_CLASSES));

        $this->actingAs($admin)->put(route('admin.tax-classes.update', $taxClass), [
            'code' => 'luxury_tax', 'name' => 'Updated Luxury Tax', 'description' => '', 'status' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tax_classes', ['id' => $taxClass->id, 'name' => 'Updated Luxury Tax', 'status' => 0]);
    }

    public function test_tax_class_code_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/tax-classes', [
            'code' => 'STANDARD_TAX', 'name' => 'Duplicate', 'status' => '1',
        ])->assertSessionHasErrors('code');
    }

    public function test_tax_class_with_rates_cannot_be_deleted_but_unused_class_can(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $standard = TaxClass::query()->where('code', 'standard_tax')->firstOrFail();

        $this->actingAs($admin)->delete(route('admin.tax-classes.destroy', $standard))->assertSessionHasErrors('tax_class');
        $this->assertDatabaseHas('tax_classes', ['id' => $standard->id]);

        $unused = TaxClass::query()->create(['code' => 'unused', 'name' => 'Unused', 'status' => true]);
        $this->actingAs($admin)->delete(route('admin.tax-classes.destroy', $unused))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('tax_classes', ['id' => $unused->id]);
    }

    public function test_admin_can_create_update_and_delete_tax_rate(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $standard = TaxClass::query()->where('code', 'standard_tax')->firstOrFail();

        $this->actingAs($admin)->post('/admin/tax-rates', [
            'tax_class_id' => $standard->id, 'country_code' => ' us ', 'region' => 'California',
            'rate' => 8.25, 'priority' => 2, 'status' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.tax-rates.index'));

        $rate = TaxRate::query()->where('country_code', 'US')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.tax-rates.update', $rate), [
            'tax_class_id' => $standard->id, 'country_code' => 'US', 'region' => '',
            'rate' => 9, 'priority' => 3, 'status' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tax_rates', ['id' => $rate->id, 'region' => null, 'rate' => 9, 'status' => 0]);

        $this->actingAs($admin)->delete(route('admin.tax-rates.destroy', $rate))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('tax_rates', ['id' => $rate->id]);
    }

    public function test_tax_rate_validation_rejects_invalid_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/tax-rates', [
            'tax_class_id' => 999999, 'country_code' => str_repeat('A', 11), 'rate' => 101,
            'priority' => -1, 'status' => '1',
        ])->assertSessionHasErrors(['tax_class_id', 'country_code', 'rate', 'priority']);
    }

    public function test_tax_service_calculates_exclusive_and_inclusive_prices(): void
    {
        $settings = app(SystemSettingService::class);
        $tax = app(TaxService::class);
        $settings->set('tax_enabled', true, 'boolean', 'tax', true);
        $settings->set('price_include_tax', false, 'boolean', 'tax', true);

        $this->assertSame([
            'rate' => 10.0, 'base_amount' => 100000.0, 'tax_amount' => 10000.0, 'total_amount' => 110000.0,
        ], $tax->calculate(100000, 'standard_tax', 'vn'));

        $settings->set('price_include_tax', true, 'boolean', 'tax', true);
        $this->assertSame([
            'rate' => 10.0, 'base_amount' => 100000.0, 'tax_amount' => 10000.0, 'total_amount' => 110000.0,
        ], $tax->calculate(110000, 'standard_tax', 'VN'));
    }

    public function test_tax_service_returns_zero_when_tax_disabled_or_rate_missing(): void
    {
        $settings = app(SystemSettingService::class);
        $tax = app(TaxService::class);
        $settings->set('tax_enabled', false, 'boolean', 'tax', true);

        $this->assertSame(0.0, $tax->calculate(100000, 'standard_tax', 'VN')['tax_amount']);

        $settings->set('tax_enabled', true, 'boolean', 'tax', true);
        $this->assertSame(0.0, $tax->calculate(100000, 'standard_tax', 'US')['tax_amount']);
    }

    public function test_seeders_are_idempotent_and_use_documented_defaults(): void
    {
        $this->seed([TaxClassSeeder::class, TaxRateSeeder::class]);

        $this->assertSame(3, TaxClass::query()->count());
        $this->assertSame(3, TaxRate::query()->count());
        $this->assertDatabaseHas('tax_rates', ['country_code' => 'VN', 'rate' => 10, 'priority' => 1, 'status' => 1]);
    }
}
