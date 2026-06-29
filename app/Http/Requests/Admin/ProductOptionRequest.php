<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $option = $this->route('productOption');
        $product = $this->route('product');
        $productId = $product instanceof Product ? $product->id : $option?->product_id;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('product_options')->where('product_id', $productId)->ignore($option)],
            'display_name' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'display_name' => filled($this->input('display_name')) ? trim((string) $this->input('display_name')) : null,
            'sort_order' => $this->input('sort_order', 0),
            'status' => $this->boolean('status'),
        ]);
    }
}
