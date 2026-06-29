<?php

namespace App\Http\Requests\Admin;

use App\Models\Banner;
use App\Services\BannerService;
use App\Services\LanguageService;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class BannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'position' => ['required', Rule::in(BannerService::POSITIONS)],
            'image' => [
                'nullable',
                'file',
                'image',
                'mimetypes:image/jpeg,image/png,image/webp',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'mobile_image' => [
                'nullable',
                'file',
                'image',
                'mimetypes:image/jpeg,image/png,image/webp',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'remove_image' => ['nullable', 'boolean'],
            'remove_mobile_image' => ['nullable', 'boolean'],
            'link_url' => ['nullable', 'string', 'max:500', $this->safeLinkRule()],
            'link_target' => ['required', Rule::in(['same_tab', 'new_tab'])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:99999'],
            'status' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'translations' => ['required', 'array'],
        ];

        foreach (app(LanguageService::class)->active() as $language) {
            $prefix = "translations.{$language->code}";
            $rules[$prefix] = ['nullable', 'array'];
            $rules["{$prefix}.title"] = ['nullable', 'string', 'max:255'];
            $rules["{$prefix}.subtitle"] = ['nullable', 'string', 'max:255'];
            $rules["{$prefix}.description"] = ['nullable', 'string', 'max:5000'];
            $rules["{$prefix}.button_text"] = ['nullable', 'string', 'max:100'];
            $rules["{$prefix}.image_alt"] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $translations = [];
        foreach (app(LanguageService::class)->active() as $language) {
            $input = (array) $this->input("translations.{$language->code}", []);
            $translations[$language->code] = [
                'title' => $this->nullableString($input['title'] ?? null),
                'subtitle' => $this->nullableString($input['subtitle'] ?? null),
                'description' => $this->nullableString($input['description'] ?? null),
                'button_text' => $this->nullableString($input['button_text'] ?? null),
                'image_alt' => $this->nullableString($input['image_alt'] ?? null),
            ];
        }

        $this->merge([
            'link_url' => $this->nullableString($this->input('link_url')),
            'sort_order' => $this->input('sort_order', 0),
            'status' => $this->boolean('status'),
            'remove_image' => $this->boolean('remove_image'),
            'remove_mobile_image' => $this->boolean('remove_mobile_image'),
            'starts_at' => $this->nullableString($this->input('starts_at')),
            'ends_at' => $this->nullableString($this->input('ends_at')),
            'translations' => $translations,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $banner = $this->banner();
            $hasDesktopImage = $this->hasFile('image')
                || ($banner?->image_path && ! $this->boolean('remove_image'));
            $defaultCode = app(LanguageService::class)->getDefault()?->code ?? 'vi';
            $defaultTitle = $this->input("translations.{$defaultCode}.title");

            if (! $hasDesktopImage && blank($defaultTitle)) {
                $validator->errors()->add('image', __('admin.banners.image_or_title_required'));
            }

            if ($this->filled('starts_at') && $this->filled('ends_at')
                && strtotime((string) $this->input('ends_at')) <= strtotime((string) $this->input('starts_at'))) {
                $validator->errors()->add('ends_at', __('admin.banners.invalid_schedule'));
            }
        });
    }

    protected function banner(): ?Banner
    {
        $banner = $this->route('banner');

        return $banner instanceof Banner ? $banner : null;
    }

    private function safeLinkRule(): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            $link = trim((string) $value);
            $isInternal = str_starts_with($link, '/') && ! str_starts_with($link, '//');
            $scheme = parse_url($link, PHP_URL_SCHEME);
            $isExternal = in_array(strtolower((string) $scheme), ['http', 'https'], true)
                && filter_var($link, FILTER_VALIDATE_URL);

            if (! $isInternal && ! $isExternal) {
                $fail(__('admin.banners.invalid_link'));
            }
        };
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
