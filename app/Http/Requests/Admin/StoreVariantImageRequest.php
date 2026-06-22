<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreVariantImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_main' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'alt_text' => $this->filled('alt_text') ? trim((string) $this->input('alt_text')) : null,
            'sort_order' => $this->filled('sort_order') ? $this->input('sort_order') : null,
            'is_main' => $this->boolean('is_main'),
            'status' => $this->boolean('status'),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->boolean('is_main') && ! $this->boolean('status')) {
                $validator->errors()->add('is_main', __('admin.messages.inactive_variant_image_cannot_main'));
            }
        });
    }
}
