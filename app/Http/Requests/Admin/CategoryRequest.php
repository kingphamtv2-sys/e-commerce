<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Services\LanguageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\Validator;

abstract class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $defaultCode = app(LanguageService::class)->getDefault()?->code ?? 'vi';
        $rules = [
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'image' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
            'translations' => ['required', 'array'],
        ];

        foreach (app(LanguageService::class)->active() as $language) {
            $prefix = "translations.{$language->code}";
            $rules[$prefix] = $language->code === $defaultCode ? ['required', 'array'] : ['nullable', 'array'];
            $rules["{$prefix}.name"] = [$language->code === $defaultCode ? 'required' : 'nullable', 'string', 'max:255'];
            $rules["{$prefix}.slug"] = ['nullable', 'string', 'max:255', $this->uniqueSlugRule($language->code)];
            $rules["{$prefix}.description"] = ['nullable', 'string'];
            $rules["{$prefix}.meta_title"] = ['nullable', 'string', 'max:255'];
            $rules["{$prefix}.meta_description"] = ['nullable', 'string'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $translations = [];

        foreach (app(LanguageService::class)->active() as $language) {
            $input = (array) $this->input("translations.{$language->code}", []);
            $name = trim((string) ($input['name'] ?? ''));
            $slug = trim((string) ($input['slug'] ?? ''));
            $translations[$language->code] = [
                'name' => $name,
                'slug' => $slug !== '' ? Str::slug($slug) : ($name !== '' ? Str::slug($name) : null),
                'description' => $this->nullableString($input['description'] ?? null),
                'meta_title' => $this->nullableString($input['meta_title'] ?? null),
                'meta_description' => $this->nullableString($input['meta_description'] ?? null),
            ];
        }

        $this->merge([
            'parent_id' => $this->filled('parent_id') ? $this->input('parent_id') : null,
            'image' => $this->nullableString($this->input('image')),
            'sort_order' => $this->input('sort_order', 0),
            'status' => $this->boolean('status'),
            'translations' => $translations,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach (app(LanguageService::class)->active() as $language) {
                $translation = (array) $this->input("translations.{$language->code}", []);
                $hasContent = collect($translation)->except('name')->contains(fn (mixed $value): bool => filled($value));

                if ($hasContent && blank($translation['name'] ?? null)) {
                    $validator->errors()->add("translations.{$language->code}.name", __('admin.messages.category_translation_name_required'));
                }
            }
        });
    }

    protected function category(): ?Category
    {
        $category = $this->route('category');

        return $category instanceof Category ? $category : null;
    }

    private function uniqueSlugRule(string $languageCode): Unique
    {
        $rule = Rule::unique('category_translations', 'slug')
            ->where(fn ($query) => $query->where('language_code', $languageCode));
        $translation = $this->category()?->categoryTranslations()->where('language_code', $languageCode)->first();

        return $translation ? $rule->ignore($translation->id) : $rule;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
