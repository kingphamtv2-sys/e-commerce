<?php

namespace Tests\Feature\Storefront;

use App\Models\Banner;
use App\Models\Currency;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerRenderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Language::query()->create(['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'is_default' => true, 'status' => true]);
        Language::query()->create(['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_default' => false, 'status' => true]);
        Currency::query()->create([
            'code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫', 'exchange_rate' => 1,
            'decimal_places' => 0, 'symbol_position' => 'after', 'is_default' => true, 'status' => true,
        ]);
    }

    public function test_catalog_renders_active_banner_with_mobile_image_and_default_translation_fallback(): void
    {
        $banner = Banner::query()->create([
            'position' => 'catalog_top', 'image_path' => 'banners/desktop.webp',
            'mobile_image_path' => 'banners/mobile.webp', 'link_url' => '/products',
            'link_target' => 'new_tab', 'sort_order' => 1, 'status' => true,
        ]);
        $banner->translations()->create([
            'language_code' => 'vi', 'title' => 'Banner tiếng Việt',
            'button_text' => 'Xem ngay', 'image_alt' => 'Ảnh banner',
        ]);

        $this->get(route('products.index', ['language' => 'en']))
            ->assertOk()
            ->assertSee('Banner tiếng Việt')
            ->assertSee('/storage/banners/desktop.webp', false)
            ->assertSee('/storage/banners/mobile.webp', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_inactive_future_and_expired_banners_do_not_render(): void
    {
        foreach ([
            ['status' => false, 'title' => 'Inactive Banner'],
            ['status' => true, 'starts_at' => now()->addDay(), 'title' => 'Future Banner'],
            ['status' => true, 'ends_at' => now()->subMinute(), 'title' => 'Expired Banner'],
        ] as $data) {
            $banner = Banner::query()->create([
                'position' => 'catalog_top', 'link_target' => 'same_tab',
                'sort_order' => 0, 'status' => $data['status'],
                'starts_at' => $data['starts_at'] ?? null, 'ends_at' => $data['ends_at'] ?? null,
            ]);
            $banner->translations()->create(['language_code' => 'vi', 'title' => $data['title']]);
        }

        $this->get(route('products.index'))
            ->assertOk()
            ->assertDontSee('Inactive Banner')
            ->assertDontSee('Future Banner')
            ->assertDontSee('Expired Banner');
    }
}
