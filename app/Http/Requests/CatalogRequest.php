<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'stock' => ['nullable', Rule::in(['in_stock', 'low_stock', 'out_of_stock'])],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc', 'name_asc', 'featured'])],
            'language' => ['nullable', 'string', 'max:10'],
            'currency' => ['nullable', 'string', 'max:10'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'keyword' => $this->filled('keyword') ? trim((string) $this->input('keyword')) : null,
            'category' => $this->filled('category') ? trim((string) $this->input('category')) : null,
            'language' => $this->filled('language') ? strtolower(trim((string) $this->input('language'))) : null,
            'currency' => $this->filled('currency') ? strtoupper(trim((string) $this->input('currency'))) : null,
        ]);
    }
}
