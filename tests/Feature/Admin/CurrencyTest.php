<?php

namespace Tests\Feature\Admin;

use App\Models\Currency;
use App\Models\User;
use App\Services\CurrencyService;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CurrencySeeder::class);
    }

    public function test_admin_can_view_currency_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/currencies')
            ->assertOk()->assertSeeInOrder(['VND', 'JPY', 'USD'])->assertSee('Add Currency');
        $this->actingAs($admin)->get('/admin/currencies/create')->assertOk()->assertSee('Currency Details');
    }

    public function test_guest_and_customer_cannot_access_currency_management(): void
    {
        $this->get('/admin/currencies')->assertRedirect(route('login'));
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get('/admin/currencies')->assertForbidden();
    }

    public function test_admin_can_create_currency_and_code_is_normalized(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Cache::put(CurrencyService::CACHE_ALL, ['stale']);

        $this->actingAs($admin)->post('/admin/currencies', $this->payload())
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.currencies.index'))
            ->assertSessionHas('success', 'Currency created successfully.');

        $this->assertDatabaseHas('currencies', ['code' => 'EUR', 'name' => 'Euro', 'is_default' => 0, 'status' => 1]);
        $this->assertTrue(Cache::missing(CurrencyService::CACHE_ALL));
    }

    public function test_currency_validation_rejects_duplicate_code_and_invalid_rate(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->from('/admin/currencies/create')
            ->post('/admin/currencies', $this->payload(['code' => 'vnd', 'exchange_rate' => 0]))
            ->assertRedirect('/admin/currencies/create')->assertSessionHasErrors(['code', 'exchange_rate']);
    }

    public function test_admin_can_update_currency(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $usd = Currency::query()->where('code', 'USD')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.currencies.update', $usd), $this->payload([
            'code' => 'usd', 'name' => 'Updated US Dollar', 'symbol' => '$', 'exchange_rate' => 26000,
            'decimal_places' => 2, 'symbol_position' => 'before', 'is_default' => '0',
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin.currencies.index'));

        $this->assertDatabaseHas('currencies', ['id' => $usd->id, 'code' => 'USD', 'name' => 'Updated US Dollar', 'exchange_rate' => 26000]);
    }

    public function test_admin_can_delete_non_default_currency(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $jpy = Currency::query()->where('code', 'JPY')->firstOrFail();

        $this->actingAs($admin)->delete(route('admin.currencies.destroy', $jpy))
            ->assertRedirect(route('admin.currencies.index'))->assertSessionHas('success', 'Currency deleted successfully.');
        $this->assertDatabaseMissing('currencies', ['id' => $jpy->id]);
    }

    public function test_default_currency_cannot_be_deleted_disabled_or_unset(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vnd = Currency::query()->where('code', 'VND')->firstOrFail();

        $this->actingAs($admin)->delete(route('admin.currencies.destroy', $vnd))->assertSessionHasErrors('currency');

        $this->actingAs($admin)->put(route('admin.currencies.update', $vnd), $this->payload([
            'code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫', 'exchange_rate' => 1,
            'status' => '0', 'is_default' => '1',
        ]))->assertSessionHasErrors('status');

        $this->actingAs($admin)->put(route('admin.currencies.update', $vnd), $this->payload([
            'code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫', 'exchange_rate' => 1,
            'status' => '1', 'is_default' => '0',
        ]))->assertSessionHasErrors('is_default');

        $this->assertTrue($vnd->fresh()->is_default);
        $this->assertTrue($vnd->fresh()->status);
    }

    public function test_active_currency_can_become_the_only_default(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $usd = Currency::query()->where('code', 'USD')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.currencies.set-default', $usd))
            ->assertSessionHasNoErrors()->assertSessionHas('success', 'Default currency updated successfully.');

        $this->assertTrue($usd->fresh()->is_default);
        $this->assertSame(1, Currency::query()->where('is_default', true)->count());
    }

    public function test_inactive_currency_cannot_be_set_as_default(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $usd = Currency::query()->where('code', 'USD')->firstOrFail();
        $usd->update(['status' => false]);

        $this->actingAs($admin)->put(route('admin.currencies.set-default', $usd))->assertSessionHasErrors('currency');
        $this->assertFalse($usd->fresh()->is_default);
    }

    public function test_currency_service_converts_formats_and_caches(): void
    {
        $service = app(CurrencyService::class);

        $this->assertSame(20.0, $service->convert(500000, 'VND', 'USD'));
        $this->assertSame(2941.0, $service->convert(500000, 'VND', 'JPY'));
        $this->assertSame(500000.0, $service->convert(20, 'USD', 'VND'));
        $this->assertSame('500,000 ₫', $service->format(500000, 'VND'));
        $this->assertSame('$20.00', $service->format(20, 'USD'));
        $this->assertSame('¥2,941', $service->format(2941, 'JPY'));
        $this->assertCount(3, $service->all());
        $this->assertCount(3, $service->active());
        $this->assertSame('VND', $service->getDefault()?->code);
        $this->assertSame('USD', $service->findByCode('usd')?->code);

        $service->clearCache();
        $this->assertTrue(Cache::missing(CurrencyService::CACHE_ALL));
        $this->assertTrue(Cache::missing(CurrencyService::CACHE_ACTIVE));
        $this->assertTrue(Cache::missing(CurrencyService::CACHE_DEFAULT));
    }

    public function test_currency_seeder_is_idempotent_with_one_default(): void
    {
        $this->seed(CurrencySeeder::class);

        $this->assertSame(3, Currency::query()->count());
        $this->assertSame(1, Currency::query()->where('is_default', true)->count());
        $this->assertDatabaseHas('currencies', ['code' => 'VND', 'symbol' => '₫', 'is_default' => 1]);
        $this->assertDatabaseHas('currencies', ['code' => 'JPY', 'symbol' => '¥']);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'code' => 'eur', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate' => 29000,
            'decimal_places' => 2, 'symbol_position' => 'after', 'thousand_separator' => ',',
            'decimal_separator' => '.', 'status' => '1', 'is_default' => '0',
        ], $overrides);
    }
}
