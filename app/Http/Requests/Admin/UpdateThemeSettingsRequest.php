<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateThemeSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $booleans = [
            'hero_enabled',
            'show_featured_categories',
            'show_featured_products',
            'show_new_arrivals',
            'show_best_sellers',
            'show_promotion_banner',
            'show_newsletter',
            'remove_logo',
            'remove_favicon',
            'remove_hero_image',
        ];

        $this->merge(collect($booleans)
            ->mapWithKeys(fn (string $key): array => [$key => $this->boolean($key)])
            ->all());
    }

    public function rules(): array
    {
        $color = ['required', 'regex:/^#[0-9a-fA-F]{6}$/'];
        $nullableUrl = ['nullable', 'url', 'max:2048'];

        return [
            'brand_name' => ['nullable', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,webp', 'max:512'],
            'remove_logo' => ['boolean'],
            'remove_favicon' => ['boolean'],
            'primary_color' => $color,
            'secondary_color' => $color,
            'text_color' => $color,
            'button_color' => $color,
            'link_color' => $color,
            'background_color' => $color,
            'hero_enabled' => ['boolean'],
            'hero_title' => ['nullable', 'string', 'max:160'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_hero_image' => ['boolean'],
            'hero_button_text' => ['nullable', 'string', 'max:80'],
            'hero_button_url' => ['nullable', 'string', 'max:2048'],
            'show_featured_categories' => ['boolean'],
            'show_featured_products' => ['boolean'],
            'show_new_arrivals' => ['boolean'],
            'show_best_sellers' => ['boolean'],
            'show_promotion_banner' => ['boolean'],
            'show_newsletter' => ['boolean'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'copyright_text' => ['nullable', 'string', 'max:180'],
            'store_description' => ['nullable', 'string', 'max:600'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:500'],
            'facebook_url' => $nullableUrl,
            'instagram_url' => $nullableUrl,
            'youtube_url' => $nullableUrl,
            'tiktok_url' => $nullableUrl,
            'custom_css' => ['nullable', 'string', 'max:12000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $url = (string) $this->input('hero_button_url', '');
                if ($url !== '' && ! str_starts_with($url, '/') && filter_var($url, FILTER_VALIDATE_URL) === false) {
                    $validator->errors()->add('hero_button_url', __('admin.theme.validation.hero_button_url'));
                }

                if ($this->filled('custom_css') && $this->containsUnsafeCss((string) $this->input('custom_css'))) {
                    $validator->errors()->add('custom_css', __('admin.theme.validation.custom_css'));
                }
            },
        ];
    }

    private function containsUnsafeCss(string $css): bool
    {
        $lower = mb_strtolower($css);

        return str_contains($lower, '<script')
            || str_contains($lower, '</script')
            || preg_match('/<[^>]+>/', $css)
            || str_contains($lower, 'javascript:')
            || str_contains($lower, 'expression(')
            || str_contains($lower, '@import');
    }
}
