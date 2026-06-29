<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'billing_same_as_shipping' => filter_var($this->input('billing_same_as_shipping'), FILTER_VALIDATE_BOOL),
        ]);
    }

    public function rules(): array
    {
        $requiredBilling = $this->boolean('billing_same_as_shipping') ? 'nullable' : 'required';

        return [
            'contact.name' => ['required', 'string', 'max:255'],
            'contact.email' => ['required', 'email', 'max:255'],
            'contact.phone' => ['required', 'string', 'max:30'],
            'shipping.full_name' => ['required', 'string', 'max:255'],
            'shipping.phone' => ['required', 'string', 'max:30'],
            'shipping.country_code' => ['required', 'string', 'max:10'],
            'shipping.province' => ['required', 'string', 'max:255'],
            'shipping.district' => ['nullable', 'string', 'max:255'],
            'shipping.ward' => ['nullable', 'string', 'max:255'],
            'shipping.address_line' => ['required', 'string', 'max:500'],
            'billing_same_as_shipping' => ['boolean'],
            'billing.full_name' => [$requiredBilling, 'string', 'max:255'],
            'billing.phone' => [$requiredBilling, 'string', 'max:30'],
            'billing.country_code' => [$requiredBilling, 'string', 'max:10'],
            'billing.province' => [$requiredBilling, 'string', 'max:255'],
            'billing.district' => ['nullable', 'string', 'max:255'],
            'billing.ward' => ['nullable', 'string', 'max:255'],
            'billing.address_line' => [$requiredBilling, 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
