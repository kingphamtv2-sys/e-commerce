<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => ['nullable', 'string', 'max:10'],
            'currency' => ['nullable', 'string', 'max:10'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'language' => $this->filled('language') ? strtolower(trim((string) $this->input('language'))) : null,
            'currency' => $this->filled('currency') ? strtoupper(trim((string) $this->input('currency'))) : null,
        ]);
    }
}
