<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSystemSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:255'],
            'site_email' => ['nullable', 'email', 'max:255'],
            'site_phone' => ['nullable', 'string', 'max:30'],
            'site_address' => ['nullable', 'string', 'max:1000'],
            'site_logo' => ['nullable', 'string', 'max:500'],
            'site_favicon' => ['nullable', 'string', 'max:500'],
            'default_language' => ['required', Rule::in(['vi', 'en', 'ja'])],
            'default_currency' => ['required', Rule::in(['VND', 'USD', 'JPY'])],
            'multi_language_enabled' => ['required', 'boolean'],
            'multi_currency_enabled' => ['required', 'boolean'],
            'tax_enabled' => ['required', 'boolean'],
            'price_include_tax' => ['required', 'boolean'],
            'default_shipping_fee' => ['required', 'numeric', 'min:0'],
            'free_shipping_min_amount' => ['nullable', 'numeric', 'min:0'],
            'order_code_prefix' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'multi_language_enabled' => $this->boolean('multi_language_enabled'),
            'multi_currency_enabled' => $this->boolean('multi_currency_enabled'),
            'tax_enabled' => $this->boolean('tax_enabled'),
            'price_include_tax' => $this->boolean('price_include_tax'),
        ]);
    }
}
