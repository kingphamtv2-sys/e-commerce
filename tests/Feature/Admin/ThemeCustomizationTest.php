<?php

namespace Tests\Feature\Admin;

use App\Models\Currency;
use App\Models\Language;
use App\Models\ThemeSetting;
use App\Models\User;
use App\Services\ThemeSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ThemeCustomizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Language::query()->create(['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'is_default' => true, 'status' => true, 'sort_order' => 1]);
        Currency::query()->create(['code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫', 'exchange_rate' => 1, 'decimal_places' => 0, 'symbol_position' => 'after', 'thousand_separator' => ',', 'decimal_separator' => '.', 'is_default' => true, 'status' => true]);
    }

    public function test_admin_can_view_theme_settings_and_guest_customer_are_blocked(): void
    {
        $this->get(route('admin.theme.edit'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['role' => 'customer']))
            ->get(route('admin.theme.edit'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.theme.edit'))
            ->assertOk()
            ->assertSee('Frontend Theme Customization')
            ->assertSee('Brand Settings');
    }

    public function test_admin_can_update_theme_settings_and_cache_is_cleared(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        app(ThemeSettingService::class)->all();
        $this->assertTrue(Cache::has(ThemeSettingService::CACHE_KEY));

        $this->actingAs($admin)
            ->patch(route('admin.theme.update'), $this->payload([
                'brand_name' => 'New Brand',
                'primary_color' => '#123456',
                'hero_title' => 'Fresh storefront',
                'footer_text' => 'Footer from admin',
                'facebook_url' => 'https://facebook.com/example',
                'show_best_sellers' => null,
                'custom_css' => '.theme-test { color: red; }',
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('theme_settings', ['key' => 'brand_name', 'value' => 'New Brand']);
        $this->assertDatabaseHas('theme_settings', ['key' => 'primary_color', 'value' => '#123456']);
        $this->assertDatabaseHas('theme_settings', ['key' => 'show_best_sellers', 'value' => '0']);
        $this->assertDatabaseHas('theme_settings', ['key' => 'custom_css', 'value' => '.theme-test { color: red; }']);
        $this->assertTrue(Cache::missing(ThemeSettingService::CACHE_KEY));
    }

    public function test_staff_cannot_update_custom_css_or_reset_theme(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->patch(route('admin.theme.update'), $this->payload(['custom_css' => '.staff { color: red; }']))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('theme_settings', ['key' => 'custom_css']);

        $this->actingAs($staff)->delete(route('admin.theme.reset'))->assertForbidden();
    }

    public function test_theme_validation_rejects_invalid_color_url_and_css(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.theme.edit'))
            ->patch(route('admin.theme.update'), $this->payload([
                'primary_color' => 'blue',
                'facebook_url' => 'not-a-url',
                'hero_button_url' => 'products',
                'custom_css' => '<script>alert(1)</script>',
            ]))
            ->assertRedirect(route('admin.theme.edit'))
            ->assertSessionHasErrors(['primary_color', 'facebook_url', 'hero_button_url', 'custom_css']);
    }

    public function test_frontend_applies_theme_values_and_reset_restores_defaults(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->patch(route('admin.theme.update'), $this->payload([
                'brand_name' => 'Theme Shop',
                'hero_title' => 'Hero From Theme',
                'hero_subtitle' => 'Subtitle From Theme',
                'footer_text' => 'Footer From Theme',
                'facebook_url' => 'https://facebook.com/example',
                'show_featured_products' => null,
            ]))
            ->assertRedirect();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Theme Shop')
            ->assertSee('Hero From Theme')
            ->assertSee('Subtitle From Theme')
            ->assertSee('Footer From Theme')
            ->assertSee('https://facebook.com/example')
            ->assertDontSee(__('storefront.featured_products'));

        $this->actingAs($admin)->delete(route('admin.theme.reset'))->assertRedirect();
        $this->assertSame(0, ThemeSetting::query()->count());
        $this->get(route('home'))->assertOk()->assertSee('Curated essentials for modern living');
    }

    public function test_logo_upload_is_stored_on_public_disk(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.theme.update'), [
                ...$this->payload(),
                'logo' => \Illuminate\Http\UploadedFile::fake()->image('logo.png', 240, 80),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $path = ThemeSetting::query()->where('key', 'logo_path')->value('value');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    private function payload(array $overrides = []): array
    {
        $payload = [
            'brand_name' => 'Theme Brand',
            'primary_color' => '#4f46e5',
            'secondary_color' => '#0f172a',
            'text_color' => '#0f172a',
            'button_color' => '#111827',
            'link_color' => '#4f46e5',
            'background_color' => '#f8fafc',
            'hero_enabled' => '1',
            'hero_title' => 'Theme hero',
            'hero_subtitle' => 'Theme subtitle',
            'hero_button_text' => 'Shop now',
            'hero_button_url' => '/products',
            'show_featured_categories' => '1',
            'show_featured_products' => '1',
            'show_new_arrivals' => '1',
            'show_best_sellers' => '1',
            'show_promotion_banner' => '1',
            'show_newsletter' => '1',
            'footer_text' => 'Theme footer',
            'copyright_text' => 'Copyright Theme',
            'store_description' => 'Theme description',
            'contact_email' => 'hello@example.com',
            'contact_phone' => '0900000000',
            'address' => 'Ho Chi Minh City',
            'facebook_url' => null,
            'instagram_url' => null,
            'youtube_url' => null,
            'tiktok_url' => null,
            'custom_css' => null,
        ];

        foreach ($overrides as $key => $value) {
            if ($value === null) {
                unset($payload[$key]);
            } else {
                $payload[$key] = $value;
            }
        }

        return $payload;
    }
}
