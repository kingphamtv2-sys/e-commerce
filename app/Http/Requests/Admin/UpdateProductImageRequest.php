<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_main' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'alt_text' => $this->filled('alt_text') ? trim((string) $this->input('alt_text')) : null,
            'is_main' => $this->boolean('is_main'),
            'status' => $this->boolean('status'),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->boolean('is_main') && ! $this->boolean('status')) {
                $validator->errors()->add('is_main', __('admin.messages.inactive_image_cannot_main'));
            }
        });
    }
}
