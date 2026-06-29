<?php

namespace App\Http\Requests\Admin;

use App\Models\Language;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateLanguageRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:10', 'alpha_dash', Rule::unique('languages', 'code')->ignore($this->route('language'))],
            'name' => ['required', 'string', 'max:100'],
            'native_name' => ['nullable', 'string', 'max:100'],
            'is_default' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtolower(trim((string) $this->input('code'))),
            'is_default' => $this->boolean('is_default'),
            'status' => $this->boolean('status'),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Language $language */
            $language = $this->route('language');

            if ($this->boolean('is_default') && ! $this->boolean('status')) {
                $validator->errors()->add('status', 'Default language must be active.');
            }

            if ($language->is_default && ! $this->boolean('status')) {
                $validator->errors()->add('status', 'Default language cannot be disabled.');
            }

            if ($language->is_default && ! $this->boolean('is_default')) {
                $validator->errors()->add('is_default', 'Set another language as default before removing this default.');
            }
        });
    }
}
