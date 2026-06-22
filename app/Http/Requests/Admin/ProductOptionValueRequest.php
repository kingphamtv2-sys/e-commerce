<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductOptionValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $value = $this->route('productOptionValue');
        $option = $this->route('productOption');
        $optionId = $option instanceof ProductOption ? $option->id : $value?->product_option_id;

        return [
            'value' => ['required', 'string', 'max:100', Rule::unique('product_option_values')->where('product_option_id', $optionId)->ignore($value)],
            'display_value' => ['nullable', 'string', 'max:100'],
            'color_code' => ['nullable', 'string', 'max:20', 'regex:/^#[0-9A-Fa-f]{3,8}$/'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'value' => trim((string) $this->input('value')),
            'display_value' => filled($this->input('display_value')) ? trim((string) $this->input('display_value')) : null,
            'color_code' => filled($this->input('color_code')) ? trim((string) $this->input('color_code')) : null,
            'sort_order' => $this->input('sort_order', 0),
            'status' => $this->boolean('status'),
        ]);
    }
}
