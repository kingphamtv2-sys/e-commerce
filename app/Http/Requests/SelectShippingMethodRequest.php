<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SelectShippingMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_method_id' => ['required', 'integer', 'exists:shipping_methods,id'],
            'shipping.country_code' => ['required', 'string', 'max:10'],
            'shipping.province' => ['required', 'string', 'max:255'],
            'shipping.district' => ['nullable', 'string', 'max:255'],
        ];
    }
}
