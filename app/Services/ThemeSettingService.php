<?php

namespace App\Services;

use App\Models\ThemeSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ThemeSettingService
{
    public const CACHE_KEY = 'frontend_theme_settings';

    public const DEFINITIONS = [
        'brand_name' => ['type' => 'text', 'group' => 'brand', 'default' => null],
        'logo_path' => ['type' => 'image', 'group' => 'brand', 'default' => null],
        'favicon_path' => ['type' => 'image', 'group' => 'brand', 'default' => null],
        'primary_color' => ['type' => 'color', 'group' => 'colors', 'default' => '#4f46e5'],
        'secondary_color' => ['type' => 'color', 'group' => 'colors', 'default' => '#0f172a'],
        'text_color' => ['type' => 'color', 'group' => 'colors', 'default' => '#0f172a'],
        'button_color' => ['type' => 'color', 'group' => 'colors', 'default' => '#111827'],
        'link_color' => ['type' => 'color', 'group' => 'colors', 'default' => '#4f46e5'],
        'background_color' => ['type' => 'color', 'group' => 'colors', 'default' => '#f8fafc'],
        'hero_enabled' => ['type' => 'boolean', 'group' => 'homepage', 'default' => true],
        'hero_title' => ['type' => 'text', 'group' => 'homepage', 'default' => 'Curated essentials for modern living'],
        'hero_subtitle' => ['type' => 'text', 'group' => 'homepage', 'default' => 'Discover thoughtful products, clear pricing, and a smooth checkout experience.'],
        'hero_image_path' => ['type' => 'image', 'group' => 'homepage', 'default' => null],
        'hero_button_text' => ['type' => 'text', 'group' => 'homepage', 'default' => 'Shop products'],
        'hero_button_url' => ['type' => 'url', 'group' => 'homepage', 'default' => '/products'],
        'show_featured_categories' => ['type' => 'boolean', 'group' => 'homepage_sections', 'default' => true],
        'show_featured_products' => ['type' => 'boolean', 'group' => 'homepage_sections', 'default' => true],
        'show_new_arrivals' => ['type' => 'boolean', 'group' => 'homepage_sections', 'default' => true],
        'show_best_sellers' => ['type' => 'boolean', 'group' => 'homepage_sections', 'default' => true],
        'show_promotion_banner' => ['type' => 'boolean', 'group' => 'homepage_sections', 'default' => true],
        'show_newsletter' => ['type' => 'boolean', 'group' => 'homepage_sections', 'default' => true],
        'footer_text' => ['type' => 'text', 'group' => 'footer', 'default' => 'Built for simple, confident shopping.'],
        'copyright_text' => ['type' => 'text', 'group' => 'footer', 'default' => null],
        'store_description' => ['type' => 'text', 'group' => 'footer', 'default' => 'A clean storefront with reliable products and helpful order tracking.'],
        'contact_email' => ['type' => 'text', 'group' => 'footer', 'default' => null],
        'contact_phone' => ['type' => 'text', 'group' => 'footer', 'default' => null],
        'address' => ['type' => 'text', 'group' => 'footer', 'default' => null],
        'facebook_url' => ['type' => 'url', 'group' => 'social', 'default' => null],
        'instagram_url' => ['type' => 'url', 'group' => 'social', 'default' => null],
        'youtube_url' => ['type' => 'url', 'group' => 'social', 'default' => null],
        'tiktok_url' => ['type' => 'url', 'group' => 'social', 'default' => null],
        'custom_css' => ['type' => 'css', 'group' => 'advanced', 'default' => null],
    ];

    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $stored = ThemeSetting::query()
                ->get(['key', 'value', 'type', 'group'])
                ->keyBy('key');

            return collect(self::DEFINITIONS)
                ->mapWithKeys(function (array $definition, string $key) use ($stored): array {
                    $setting = $stored->get($key);
                    $value = $setting
                        ? $this->cast($setting->value, $setting->type)
                        : $definition['default'];

                    return [$key => $value];
                })
                ->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function grouped(): array
    {
        $values = $this->all();

        return collect(self::DEFINITIONS)
            ->groupBy('group')
            ->map(fn ($definitions) => $definitions->mapWithKeys(fn (array $definition, string $key): array => [$key => $values[$key] ?? $definition['default']])->all())
            ->all();
    }

    public function update(array $data, bool $canUpdateCss): void
    {
        foreach (['logo', 'favicon', 'hero_image'] as $uploadKey) {
            $pathKey = $uploadKey === 'hero_image' ? 'hero_image_path' : $uploadKey.'_path';

            if (! empty($data['remove_'.$uploadKey])) {
                $this->deleteCurrentImage($pathKey);
                $data[$pathKey] = null;
            }

            if (($data[$uploadKey] ?? null) instanceof UploadedFile) {
                $this->deleteCurrentImage($pathKey);
                $data[$pathKey] = $data[$uploadKey]->store('theme', 'public');
            }
        }

        if (! $canUpdateCss) {
            unset($data['custom_css']);
        }

        foreach (self::DEFINITIONS as $key => $definition) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            ThemeSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $this->serialize($data[$key], $definition['type']),
                    'type' => $definition['type'],
                    'group' => $definition['group'],
                ],
            );
        }

        $this->clearCache();
    }

    public function reset(): void
    {
        $this->deleteCurrentImage('logo_path');
        $this->deleteCurrentImage('favicon_path');
        $this->deleteCurrentImage('hero_image_path');
        ThemeSetting::query()->whereIn('key', array_keys(self::DEFINITIONS))->delete();
        $this->clearCache();
    }

    public function imageUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function cssVariables(): string
    {
        $theme = $this->all();

        return collect([
            '--theme-primary' => $theme['primary_color'],
            '--theme-secondary' => $theme['secondary_color'],
            '--theme-text' => $theme['text_color'],
            '--theme-button' => $theme['button_color'],
            '--theme-link' => $theme['link_color'],
            '--theme-bg' => $theme['background_color'],
        ])->map(fn (string $value, string $key): string => "{$key}: {$value};")->implode(' ');
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function deleteCurrentImage(string $key): void
    {
        $path = ThemeSetting::query()->where('key', $key)->value('value');
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function cast(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default => $value,
        };
    }

    private function serialize(mixed $value, string $type): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $type === 'boolean'
            ? (filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0')
            : (string) $value;
    }
}
