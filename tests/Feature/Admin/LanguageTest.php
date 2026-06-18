<?php

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\User;
use App\Services\LanguageService;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(LanguageSeeder::class);
    }

    public function test_admin_can_view_language_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/languages')
            ->assertOk()
            ->assertSeeInOrder(['Vietnamese', 'English', 'Japanese'])
            ->assertSee('Thêm ngôn ngữ');

        $this->actingAs($admin)->get('/admin/languages/create')->assertOk()->assertSee('Thông tin ngôn ngữ');
    }

    public function test_guest_and_customer_cannot_access_language_management(): void
    {
        $this->get('/admin/languages')->assertRedirect(route('login'));

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get('/admin/languages')->assertForbidden();
    }

    public function test_admin_labels_follow_the_default_language(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/languages')
            ->assertOk()
            ->assertSee('Ngôn ngữ')
            ->assertSee('Thiết lập hệ thống')
            ->assertSee('Thêm ngôn ngữ');

        $english = Language::query()->where('code', 'en')->firstOrFail();
        app(LanguageService::class)->setDefault($english);

        $this->actingAs($admin)
            ->get('/admin/languages')
            ->assertOk()
            ->assertSee('Languages')
            ->assertSee('System Settings')
            ->assertSee('Add Language');
    }

    public function test_admin_can_create_language_and_code_is_normalized(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Cache::put(LanguageService::CACHE_ALL, ['stale']);

        $this->actingAs($admin)
            ->post('/admin/languages', $this->payload(['code' => 'FR', 'name' => 'French', 'native_name' => 'Français', 'sort_order' => 4]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.languages.index'))
            ->assertSessionHas('success', 'Tạo ngôn ngữ thành công.');

        $this->assertDatabaseHas('languages', ['code' => 'fr', 'name' => 'French', 'status' => 1, 'is_default' => 0]);
        $this->assertTrue(Cache::missing(LanguageService::CACHE_ALL));
    }

    public function test_language_code_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from('/admin/languages/create')
            ->post('/admin/languages', $this->payload(['code' => 'VI']))
            ->assertRedirect('/admin/languages/create')
            ->assertSessionHasErrors('code');
    }

    public function test_admin_can_update_language(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $english = Language::query()->where('code', 'en')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.languages.update', $english), $this->payload([
                'code' => 'en',
                'name' => 'English Updated',
                'native_name' => 'English',
                'sort_order' => 8,
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.languages.index'));

        $this->assertDatabaseHas('languages', ['id' => $english->id, 'name' => 'English Updated', 'sort_order' => 8]);
    }

    public function test_admin_can_delete_non_default_language(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $japanese = Language::query()->where('code', 'ja')->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.languages.destroy', $japanese))
            ->assertRedirect(route('admin.languages.index'))
            ->assertSessionHas('success', 'Xóa ngôn ngữ thành công.');

        $this->assertDatabaseMissing('languages', ['id' => $japanese->id]);
    }

    public function test_default_language_cannot_be_deleted_disabled_or_unset(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vietnamese = Language::query()->where('code', 'vi')->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.languages.destroy', $vietnamese))
            ->assertSessionHasErrors('language');
        $this->assertDatabaseHas('languages', ['id' => $vietnamese->id]);

        $this->actingAs($admin)
            ->from(route('admin.languages.edit', $vietnamese))
            ->put(route('admin.languages.update', $vietnamese), $this->payload([
                'code' => 'vi',
                'name' => 'Vietnamese',
                'status' => '0',
                'is_default' => '1',
            ]))
            ->assertSessionHasErrors('status');

        $this->actingAs($admin)
            ->from(route('admin.languages.edit', $vietnamese))
            ->put(route('admin.languages.update', $vietnamese), $this->payload([
                'code' => 'vi',
                'name' => 'Vietnamese',
                'status' => '1',
                'is_default' => '0',
            ]))
            ->assertSessionHasErrors('is_default');

        $this->assertTrue($vietnamese->fresh()->is_default);
        $this->assertTrue($vietnamese->fresh()->status);
    }

    public function test_admin_can_set_active_language_as_the_only_default(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $english = Language::query()->where('code', 'en')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.languages.set-default', $english))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Default language updated successfully.');

        $this->assertTrue($english->fresh()->is_default);
        $this->assertSame(1, Language::query()->where('is_default', true)->count());
        $this->assertFalse(Language::query()->where('code', 'vi')->firstOrFail()->is_default);
    }

    public function test_inactive_language_cannot_be_set_as_default(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $english = Language::query()->where('code', 'en')->firstOrFail();
        $english->update(['status' => false]);

        $this->actingAs($admin)
            ->put(route('admin.languages.set-default', $english))
            ->assertSessionHasErrors('language');

        $this->assertFalse($english->fresh()->is_default);
        $this->assertSame('vi', Language::query()->default()->firstOrFail()->code);
    }

    public function test_language_service_returns_cached_language_sets(): void
    {
        $service = app(LanguageService::class);

        $this->assertCount(3, $service->all());
        $this->assertCount(3, $service->active());
        $this->assertSame('vi', $service->getDefault()?->code);
        $this->assertSame('ja', $service->findByCode('JA')?->code);
        $this->assertTrue(Cache::has(LanguageService::CACHE_ALL));
        $this->assertTrue(Cache::has(LanguageService::CACHE_ACTIVE));
        $this->assertTrue(Cache::has(LanguageService::CACHE_DEFAULT));

        $service->clearCache();
        $this->assertTrue(Cache::missing(LanguageService::CACHE_ALL));
        $this->assertTrue(Cache::missing(LanguageService::CACHE_ACTIVE));
        $this->assertTrue(Cache::missing(LanguageService::CACHE_DEFAULT));
    }

    public function test_language_seeder_is_idempotent_and_preserves_one_default(): void
    {
        $this->seed(LanguageSeeder::class);

        $this->assertSame(3, Language::query()->count());
        $this->assertSame(1, Language::query()->where('is_default', true)->count());
        $this->assertDatabaseHas('languages', ['code' => 'vi', 'native_name' => 'Tiếng Việt', 'is_default' => 1, 'status' => 1]);
        $this->assertDatabaseHas('languages', ['code' => 'ja', 'native_name' => '日本語', 'status' => 1]);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'code' => 'fr',
            'name' => 'French',
            'native_name' => 'Français',
            'status' => '1',
            'is_default' => '0',
            'sort_order' => 4,
        ], $overrides);
    }
}
