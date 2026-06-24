<?php

namespace Tests\Feature\Admin;

use App\Models\Banner;
use App\Models\Currency;
use App\Models\Language;
use App\Models\User;
use App\Services\BannerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    private Language $vietnamese;

    private Language $english;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->vietnamese = Language::query()->create([
            'code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt',
            'is_default' => true, 'status' => true, 'sort_order' => 0,
        ]);
        $this->english = Language::query()->create([
            'code' => 'en', 'name' => 'English', 'native_name' => 'English',
            'is_default' => false, 'status' => true, 'sort_order' => 1,
        ]);
        Currency::query()->create([
            'code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫',
            'exchange_rate' => 1, 'decimal_places' => 0, 'symbol_position' => 'after',
            'is_default' => true, 'status' => true,
        ]);
    }

    public function test_admin_banner_routes_are_protected(): void
    {
        $this->get(route('admin.banners.index'))->assertRedirect(route('login'));
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get(route('admin.banners.index'))->assertForbidden();
    }

    public function test_admin_can_create_banner_with_images_and_translations(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->post(route('admin.banners.store'), $this->payload());

        $banner = Banner::query()->firstOrFail();
        $response->assertSessionHasNoErrors()->assertRedirect(route('admin.banners.edit', $banner));
        $this->assertDatabaseHas('banners', [
            'id' => $banner->id, 'position' => 'catalog_top', 'link_target' => 'new_tab',
            'sort_order' => 2, 'status' => true,
        ]);
        $this->assertDatabaseHas('banner_translations', [
            'banner_id' => $banner->id, 'language_code' => 'vi', 'title' => 'Khuyến mãi mùa hè',
        ]);
        $this->assertDatabaseHas('banner_translations', [
            'banner_id' => $banner->id, 'language_code' => 'en', 'title' => 'Summer Sale',
        ]);
        Storage::disk('public')->assertExists($banner->image_path);
        Storage::disk('public')->assertExists($banner->mobile_image_path);
    }

    public function test_banner_validation_rejects_invalid_link_schedule_and_missing_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = $this->payload();
        unset($payload['image']);
        $payload['link_url'] = 'javascript:alert(1)';
        $payload['starts_at'] = now()->addDay()->format('Y-m-d H:i:s');
        $payload['ends_at'] = now()->format('Y-m-d H:i:s');
        $payload['translations']['vi']['title'] = '';

        $this->actingAs($admin)->post(route('admin.banners.store'), $payload)
            ->assertSessionHasErrors(['image', 'link_url', 'ends_at']);
    }

    public function test_public_banner_uses_schedule_sort_order_and_translation_fallback(): void
    {
        $first = $this->banner(['sort_order' => 1]);
        $first->translations()->create(['language_code' => 'vi', 'title' => 'Banner mặc định', 'image_alt' => 'Alt mặc định']);
        $second = $this->banner(['sort_order' => 2]);
        $second->translations()->create(['language_code' => 'en', 'title' => 'English Banner']);
        $future = $this->banner(['sort_order' => 0, 'starts_at' => now()->addDay()]);
        $future->translations()->create(['language_code' => 'vi', 'title' => 'Future']);
        $expired = $this->banner(['sort_order' => 0, 'ends_at' => now()->subMinute()]);
        $expired->translations()->create(['language_code' => 'vi', 'title' => 'Expired']);
        $inactive = $this->banner(['sort_order' => 0, 'status' => false]);
        $inactive->translations()->create(['language_code' => 'vi', 'title' => 'Inactive']);

        $banners = app(BannerService::class)->forPosition('catalog_top', $this->english, $this->vietnamese);

        $this->assertSame([$first->id, $second->id], $banners->pluck('id')->all());
        $this->assertSame('Banner mặc định', $banners->first()->displayTranslation->title);
        $this->assertSame('English Banner', $banners->last()->displayTranslation->title);
    }

    public function test_banner_list_uses_custom_delete_modal_and_ajax_delete_removes_files(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $banner = $this->banner(['image_path' => 'banners/delete.webp']);
        $banner->translations()->create(['language_code' => 'vi', 'title' => 'Banner cần xóa']);
        Storage::disk('public')->put('banners/delete.webp', 'image');

        $this->actingAs($admin)->get(route('admin.banners.index'))
            ->assertOk()
            ->assertSee('data-async-delete', false)
            ->assertSee('data-admin-delete-modal', false)
            ->assertDontSee('confirm(', false);

        $this->actingAs($admin)->deleteJson(route('admin.banners.destroy', $banner))
            ->assertOk()->assertJsonPath('success', true);
        $this->assertSoftDeleted('banners', ['id' => $banner->id]);
        Storage::disk('public')->assertMissing('banners/delete.webp');
    }

    private function payload(): array
    {
        return [
            'position' => 'catalog_top',
            'image' => $this->fakeImage('desktop.png'),
            'mobile_image' => $this->fakeImage('mobile.png'),
            'link_url' => 'https://example.com/sale',
            'link_target' => 'new_tab',
            'sort_order' => 2,
            'status' => 1,
            'starts_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'translations' => [
                'vi' => [
                    'title' => 'Khuyến mãi mùa hè', 'subtitle' => 'Giảm giá',
                    'description' => 'Ưu đãi giới hạn', 'button_text' => 'Mua ngay', 'image_alt' => 'Khuyến mãi mùa hè',
                ],
                'en' => [
                    'title' => 'Summer Sale', 'subtitle' => 'Discount',
                    'description' => 'Limited offer', 'button_text' => 'Shop now', 'image_alt' => 'Summer sale',
                ],
            ],
        ];
    }

    private function banner(array $overrides = []): Banner
    {
        return Banner::query()->create(array_merge([
            'position' => 'catalog_top',
            'link_target' => 'same_tab',
            'sort_order' => 0,
            'status' => true,
        ], $overrides));
    }

    private function fakeImage(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
        );
    }
}
