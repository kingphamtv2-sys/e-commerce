<?php

namespace App\Http\Requests\Admin;

use App\Models\ShippingZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShippingZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => $this->filled('code') ? strtoupper(trim((string) $this->input('code'))) : null,
            'countries' => $this->lines('countries', true),
            'cities' => $this->lines('cities'),
            'districts' => $this->lines('districts'),
            'sort_order' => (int) $this->input('sort_order', 0),
            'status' => $this->input('status', ShippingZone::STATUS_ACTIVE),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string', 'max:80'],
            'cities' => ['nullable', 'array'],
            'cities.*' => ['string', 'max:120'],
            'districts' => ['nullable', 'array'],
            'districts.*' => ['string', 'max:120'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in([ShippingZone::STATUS_ACTIVE, ShippingZone::STATUS_INACTIVE])],
        ];
    }

    /** @return array<int, string>|null */
    private function lines(string $key, bool $upper = false): ?array
    {
        $value = $this->input($key);
        if (is_array($value)) {
            $lines = $value;
        } else {
            $lines = preg_split('/\r\n|\r|\n/', (string) $value) ?: [];
        }

        $lines = collect($lines)
            ->map(fn (mixed $line): string => trim((string) $line))
            ->filter()
            ->map(fn (string $line): string => $upper ? strtoupper($line) : $line)
            ->unique(fn (string $line): string => mb_strtolower($line))
            ->values()
            ->all();

        return $lines === [] ? null : $lines;
    }
}
