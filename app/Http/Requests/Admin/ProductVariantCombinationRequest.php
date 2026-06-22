<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductVariantCombinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $variant = $this->variant();

        return [
            'sku' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('product_variants', 'sku')->ignore($variant)],
            'name' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'boolean'],
            'option_values' => ['required', 'array'],
            'option_values.*' => ['required', 'integer', Rule::exists('product_option_values', 'id')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sku' => trim((string) $this->input('sku')),
            'name' => filled($this->input('name')) ? trim((string) $this->input('name')) : null,
            'price' => $this->filled('price') ? $this->input('price') : null,
            'sale_price' => $this->filled('sale_price') ? $this->input('sale_price') : null,
            'status' => $this->boolean('status'),
            'option_values' => collect((array) $this->input('option_values', []))
                ->mapWithKeys(fn ($value, $optionId): array => [(int) $optionId => filled($value) ? (int) $value : null])
                ->all(),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $product = $this->product();
            if (! $product) {
                return;
            }

            $options = $product->productOptions()->active()->with(['values' => fn ($query) => $query->active()])->get();
            $selected = collect((array) $this->input('option_values'));

            if ($options->isEmpty()) {
                $validator->errors()->add('option_values', __('admin.messages.product_options_required'));

                return;
            }

            foreach ($options as $option) {
                $valueId = (int) $selected->get($option->id);
                if (! $valueId || ! $option->values->contains('id', $valueId)) {
                    $validator->errors()->add("option_values.{$option->id}", __('admin.messages.variant_option_value_required', ['option' => $option->label()]));
                }
            }

            if ($selected->keys()->map(fn ($id): int => (int) $id)->diff($options->pluck('id'))->isNotEmpty()) {
                $validator->errors()->add('option_values', __('admin.messages.invalid_variant_option_value'));
            }

            if (strcasecmp($product->sku, (string) $this->input('sku')) === 0 || Product::query()->where('sku', $this->input('sku'))->exists()) {
                $validator->errors()->add('sku', __('admin.messages.product_variant_sku_conflict'));
            }

            $effectivePrice = $this->input('price') ?? $product->price;
            if ($this->input('sale_price') !== null && (float) $this->input('sale_price') > (float) $effectivePrice) {
                $validator->errors()->add('sale_price', __('admin.messages.sale_price_too_high'));
            }
        });
    }

    public function product(): ?Product
    {
        $product = $this->route('product');

        return $product instanceof Product ? $product : $this->variant()?->product;
    }

    private function variant(): ?ProductVariant
    {
        $variant = $this->route('productVariant');

        return $variant instanceof ProductVariant ? $variant : null;
    }
}
