<?php

namespace App\Http\Requests\Admin;

use App\Models\Currency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCurrencyRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:10', 'alpha_num', Rule::unique('currencies', 'code')->ignore($this->route('currency'))],
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:10'],
            'exchange_rate' => ['required', 'numeric', 'gt:0'],
            'decimal_places' => ['required', 'integer', 'min:0', 'max:6'],
            'symbol_position' => ['required', Rule::in(['before', 'after'])],
            'thousand_separator' => ['nullable', 'string', 'max:5'],
            'decimal_separator' => ['nullable', 'string', 'max:5'],
            'status' => ['required', 'boolean'],
            'is_default' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'status' => $this->boolean('status'),
            'is_default' => $this->boolean('is_default'),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Currency $currency */
            $currency = $this->route('currency');

            if ($this->boolean('is_default') && ! $this->boolean('status')) {
                $validator->errors()->add('status', 'Default currency must be active.');
            }

            if ($currency->is_default && ! $this->boolean('status')) {
                $validator->errors()->add('status', 'Default currency cannot be disabled.');
            }

            if ($currency->is_default && ! $this->boolean('is_default')) {
                $validator->errors()->add('is_default', 'Set another currency as default before removing this default.');
            }
        });
    }
}
