@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
        <p class="font-bold">{{ __('admin.currencies.review') }}</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ $action }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
        <h2 class="font-bold text-slate-950">{{ __('admin.currencies.details') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('admin.currencies.details_desc') }}</p>
    </div>

    @php($inputClass = 'mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500')
    <div class="grid gap-6 p-6 md:grid-cols-2">
        <div>
            <label for="code" class="text-sm font-semibold text-slate-800">{{ __('admin.currencies.code') }} *</label>
            <input id="code" name="code" maxlength="10" value="{{ old('code', $currency->code) }}" class="{{ $inputClass }} uppercase" required>
            @error('code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="name" class="text-sm font-semibold text-slate-800">{{ __('admin.currencies.name') }} *</label>
            <input id="name" name="name" maxlength="100" value="{{ old('name', $currency->name) }}" class="{{ $inputClass }}" required>
            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="symbol" class="text-sm font-semibold text-slate-800">{{ __('admin.currencies.symbol') }} *</label>
            <input id="symbol" name="symbol" maxlength="10" value="{{ old('symbol', $currency->symbol) }}" class="{{ $inputClass }}" required>
            @error('symbol') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="exchange_rate" class="text-sm font-semibold text-slate-800">{{ __('admin.currencies.exchange_rate') }} *</label>
            <input id="exchange_rate" name="exchange_rate" type="number" min="0.000001" step="0.000001" value="{{ old('exchange_rate', $currency->exchange_rate) }}" class="{{ $inputClass }}" required>
            @error('exchange_rate') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="decimal_places" class="text-sm font-semibold text-slate-800">{{ __('admin.currencies.decimal_places') }} *</label>
            <input id="decimal_places" name="decimal_places" type="number" min="0" max="6" value="{{ old('decimal_places', $currency->decimal_places) }}" class="{{ $inputClass }}" required>
            @error('decimal_places') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="symbol_position" class="text-sm font-semibold text-slate-800">{{ __('admin.currencies.symbol_position') }} *</label>
            <select id="symbol_position" name="symbol_position" class="{{ $inputClass }}" required>
                <option value="before" @selected(old('symbol_position', $currency->symbol_position) === 'before')>{{ __('admin.currencies.before') }}</option>
                <option value="after" @selected(old('symbol_position', $currency->symbol_position) === 'after')>{{ __('admin.currencies.after') }}</option>
            </select>
        </div>
        <div>
            <label for="thousand_separator" class="text-sm font-semibold text-slate-800">{{ __('admin.currencies.thousand_separator') }}</label>
            <input id="thousand_separator" name="thousand_separator" maxlength="5" value="{{ old('thousand_separator', $currency->thousand_separator) }}" class="{{ $inputClass }}">
            @error('thousand_separator') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="decimal_separator" class="text-sm font-semibold text-slate-800">{{ __('admin.currencies.decimal_separator') }}</label>
            <input id="decimal_separator" name="decimal_separator" maxlength="5" value="{{ old('decimal_separator', $currency->decimal_separator) }}" class="{{ $inputClass }}">
            @error('decimal_separator') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-slate-200 p-4">
            <span><span class="block text-sm font-semibold text-slate-800">{{ __('admin.common.active') }}</span><span class="mt-1 block text-xs text-slate-500">{{ __('admin.currencies.active_desc') }}</span></span>
            <input name="status" type="checkbox" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-indigo-600" @checked(old('status', $currency->exists ? $currency->status : true))>
        </label>
        <label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-slate-200 p-4">
            <span><span class="block text-sm font-semibold text-slate-800">{{ __('admin.currencies.default_currency') }}</span><span class="mt-1 block text-xs text-slate-500">{{ __('admin.currencies.default_desc') }}</span></span>
            <input name="is_default" type="checkbox" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-indigo-600" @checked(old('is_default', $currency->is_default))>
        </label>
    </div>

    <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50/70 px-6 py-4">
        <a href="{{ route('admin.currencies.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">{{ __('admin.common.back') }}</a>
        <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">{{ $submitLabel }}</button>
    </div>
</form>
