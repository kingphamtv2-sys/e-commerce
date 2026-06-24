<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderFulfillmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fulfillment_status' => ['required', Rule::in(['processing', 'shipped', 'delivered', 'cancelled'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
