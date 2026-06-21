<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaxRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tax_class_id' => ['required', 'integer', 'exists:tax_classes,id'],
            'country_code' => ['nullable', 'string', 'max:10'],
            'region' => ['nullable', 'string', 'max:100'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'priority' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $countryCode = trim((string) $this->input('country_code'));
        $region = trim((string) $this->input('region'));

        $this->merge([
            'country_code' => $countryCode === '' ? null : strtoupper($countryCode),
            'region' => $region === '' ? null : $region,
            'priority' => $this->input('priority', 0),
            'status' => $this->boolean('status'),
        ]);
    }
}
