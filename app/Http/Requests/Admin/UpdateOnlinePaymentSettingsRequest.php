<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOnlinePaymentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'name' => ['required_if:enabled,true', 'nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'instruction' => ['nullable', 'string', 'max:2000'],
            'gateway_code' => ['required_if:enabled,true', Rule::in(['mock'])],
            'environment' => ['required', Rule::in(['sandbox', 'live'])],
            'secret_key' => ['nullable', 'string', 'min:16', 'max:500'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_order_amount' => ['nullable', 'numeric', 'min:0', 'gte:min_order_amount'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['enabled' => $this->boolean('enabled')]);
    }
}
