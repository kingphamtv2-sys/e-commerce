<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_status' => ['required', Rule::in(['unpaid', 'pending', 'paid', 'failed', 'cancelled'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
