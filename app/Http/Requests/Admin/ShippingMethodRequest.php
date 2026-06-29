<?php

namespace App\Http\Requests\Admin;

use App\Models\ShippingMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShippingMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtolower(trim((string) $this->input('code'))),
            'shipping_zone_id' => $this->filled('shipping_zone_id') ? $this->input('shipping_zone_id') : null,
            'base_fee' => $this->input('base_fee', 0),
            'sort_order' => (int) $this->input('sort_order', 0),
            'status' => $this->input('status', ShippingMethod::STATUS_ACTIVE),
        ]);
    }

    public function rules(): array
    {
        return [
            'shipping_zone_id' => ['nullable', 'integer', 'exists:shipping_zones,id'],
            'code' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in([
                ShippingMethod::TYPE_FLAT_RATE,
                ShippingMethod::TYPE_FREE_SHIPPING,
                ShippingMethod::TYPE_PICKUP,
            ])],
            'base_fee' => ['required', 'numeric', 'gte:0'],
            'free_shipping_min_amount' => ['nullable', 'numeric', 'gte:0'],
            'min_order_amount' => ['nullable', 'numeric', 'gte:0'],
            'max_order_amount' => ['nullable', 'numeric', 'gte:min_order_amount'],
            'estimated_delivery_min_days' => ['nullable', 'integer', 'min:0'],
            'estimated_delivery_max_days' => ['nullable', 'integer', 'gte:estimated_delivery_min_days'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in([ShippingMethod::STATUS_ACTIVE, ShippingMethod::STATUS_INACTIVE])],
        ];
    }
}
