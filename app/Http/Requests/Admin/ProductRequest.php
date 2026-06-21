<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\LanguageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\Validator;

abstract class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->product();
        $categoryId = $product?->category_id;
        $taxClassId = $product?->tax_class_id;
        $defaultCode = app(LanguageService::class)->getDefault()?->code ?? 'vi';
        $rules = [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where(fn ($query) => $query->whereNull('deleted_at')->where(fn ($query) => $query->where('status', true)->when($categoryId, fn ($query) => $query->orWhere('id', $categoryId))))],
            'tax_class_id' => ['nullable', 'integer', Rule::exists('tax_classes', 'id')->where(fn ($query) => $query->where('status', true)->when($taxClassId, fn ($query) => $query->orWhere('id', $taxClassId)))],
            'sku' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('products', 'sku')->ignore($product)],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'translations' => ['required', 'array'],
            'variants' => ['nullable', 'array'],
        ];

        foreach (app(LanguageService::class)->active() as $language) {
            $prefix = "translations.{$language->code}";
            $rules[$prefix] = $language->code === $defaultCode ? ['required', 'array'] : ['nullable', 'array'];
            $rules["{$prefix}.name"] = [$language->code === $defaultCode ? 'required' : 'nullable', 'string', 'max:255'];
            $rules["{$prefix}.slug"] = ['nullable', 'string', 'max:255', $this->uniqueSlugRule($language->code)];
            $rules["{$prefix}.short_description"] = ['nullable', 'string'];
            $rules["{$prefix}.description"] = ['nullable', 'string'];
            $rules["{$prefix}.meta_title"] = ['nullable', 'string', 'max:255'];
            $rules["{$prefix}.meta_description"] = ['nullable', 'string'];
        }

        foreach ((array) $this->input('variants', []) as $index => $variant) {
            $variantId = isset($variant['id']) ? (int) $variant['id'] : null;
            $variantUnique = Rule::unique('product_variants', 'sku');

            if ($variantId) {
                $variantUnique->ignore($variantId);
            }

            $rules["variants.{$index}.id"] = ['nullable', 'integer', Rule::exists('product_variants', 'id')->when($product, fn ($rule) => $rule->where('product_id', $product->id))];
            $rules["variants.{$index}.sku"] = ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/', $variantUnique];
            $rules["variants.{$index}.name"] = ['required', 'string', 'max:255'];
            $rules["variants.{$index}.price"] = ['nullable', 'numeric', 'min:0'];
            $rules["variants.{$index}.sale_price"] = ['nullable', 'numeric', 'min:0'];
            $rules["variants.{$index}.status"] = ['required', 'boolean'];
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
                'short_description' => $this->nullableString($input['short_description'] ?? null),
                'description' => $this->nullableString($input['description'] ?? null),
                'meta_title' => $this->nullableString($input['meta_title'] ?? null),
                'meta_description' => $this->nullableString($input['meta_description'] ?? null),
            ];
        }

        $variants = collect((array) $this->input('variants', []))
            ->map(function (array $variant): array {
                return [
                    'id' => ! empty($variant['id']) ? (int) $variant['id'] : null,
                    'sku' => trim((string) ($variant['sku'] ?? '')),
                    'name' => trim((string) ($variant['name'] ?? '')),
                    'price' => $this->nullableNumber($variant['price'] ?? null),
                    'sale_price' => $this->nullableNumber($variant['sale_price'] ?? null),
                    'status' => filter_var($variant['status'] ?? false, FILTER_VALIDATE_BOOL),
                ];
            })
            ->filter(fn (array $variant): bool => $variant['id'] || $variant['sku'] !== '' || $variant['name'] !== '' || $variant['price'] !== null || $variant['sale_price'] !== null)
            ->values()
            ->all();

        $this->merge([
            'tax_class_id' => $this->filled('tax_class_id') ? $this->input('tax_class_id') : null,
            'sku' => trim((string) $this->input('sku')),
            'sale_price' => $this->nullableNumber($this->input('sale_price')),
            'cost_price' => $this->nullableNumber($this->input('cost_price')),
            'status' => $this->boolean('status'),
            'is_featured' => $this->boolean('is_featured'),
            'translations' => $translations,
            'variants' => $variants,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach (app(LanguageService::class)->active() as $language) {
                $translation = (array) $this->input("translations.{$language->code}", []);
                $hasContent = collect($translation)->except('name')->contains(fn (mixed $value): bool => filled($value));

                if ($hasContent && blank($translation['name'] ?? null)) {
                    $validator->errors()->add("translations.{$language->code}.name", __('admin.messages.product_translation_name_required'));
                }
            }

            $productSku = strtolower((string) $this->input('sku'));
            $variantSkus = [];

            if (ProductVariant::query()->where('sku', $this->input('sku'))->exists()) {
                $validator->errors()->add('sku', __('admin.messages.product_variant_sku_conflict'));
            }

            foreach ((array) $this->input('variants', []) as $index => $variant) {
                $sku = strtolower((string) ($variant['sku'] ?? ''));
                $effectivePrice = $variant['price'] ?? $this->input('price');

                if ($sku === $productSku || in_array($sku, $variantSkus, true) || Product::query()->where('sku', $variant['sku'] ?? '')->exists()) {
                    $validator->errors()->add("variants.{$index}.sku", __('admin.messages.product_variant_sku_conflict'));
                }

                if (($variant['sale_price'] ?? null) !== null && (float) $variant['sale_price'] > (float) $effectivePrice) {
                    $validator->errors()->add("variants.{$index}.sale_price", __('admin.messages.sale_price_too_high'));
                }

                $variantSkus[] = $sku;
            }
        });
    }

    protected function product(): ?Product
    {
        $product = $this->route('product');

        return $product instanceof Product ? $product : null;
    }

    private function uniqueSlugRule(string $languageCode): Unique
    {
        $rule = Rule::unique('product_translations', 'slug')->where(fn ($query) => $query->where('language_code', $languageCode));
        $translation = $this->product()?->productTranslations()->where('language_code', $languageCode)->first();

        return $translation ? $rule->ignore($translation->id) : $rule;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function nullableNumber(mixed $value): mixed
    {
        return $value === null || $value === '' ? null : $value;
    }
}
