<div data-variant-selector="{{ $option->id }}">
    <label class="text-xs font-bold text-slate-600">{{ $option->label() }} *</label>
    <select data-option-select="{{ $option->id }}" name="option_values[{{ $option->id }}]" required class="mt-1.5 block w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">{{ __('admin.variant_combinations.choose_value') }}</option>
        @foreach($option->values->where('status', true) as $value)<option value="{{ $value->id }}">{{ $value->label() }}</option>@endforeach
    </select>
</div>
