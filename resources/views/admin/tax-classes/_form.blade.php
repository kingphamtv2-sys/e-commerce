@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
        <p class="font-bold">{{ __('admin.tax_classes.review') }}</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ $action }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
        <h2 class="font-bold text-slate-950">{{ __('admin.tax_classes.details') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('admin.tax_classes.details_desc') }}</p>
    </div>
    @php($inputClass = 'mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500')
    <div class="grid gap-6 p-6 md:grid-cols-2">
        <div>
            <label for="code" class="text-sm font-semibold text-slate-800">{{ __('admin.tax_classes.code') }} *</label>
            <input id="code" name="code" maxlength="100" value="{{ old('code', $taxClass->code) }}" class="{{ $inputClass }} lowercase" required>
            @error('code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="name" class="text-sm font-semibold text-slate-800">{{ __('admin.tax_classes.name') }} *</label>
            <input id="name" name="name" maxlength="255" value="{{ old('name', $taxClass->name) }}" class="{{ $inputClass }}" required>
            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
            <label for="description" class="text-sm font-semibold text-slate-800">{{ __('admin.tax_classes.description') }}</label>
            <textarea id="description" name="description" rows="4" class="{{ $inputClass }}">{{ old('description', $taxClass->description) }}</textarea>
        </div>
        <label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-slate-200 p-4 md:col-span-2">
            <span><span class="block text-sm font-semibold text-slate-800">{{ __('admin.common.active') }}</span><span class="mt-1 block text-xs text-slate-500">{{ __('admin.tax_classes.active_desc') }}</span></span>
            <input name="status" type="checkbox" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-indigo-600" @checked(old('status', $taxClass->exists ? $taxClass->status : true))>
        </label>
    </div>
    <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50/70 px-6 py-4">
        <a href="{{ route('admin.tax-classes.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">{{ __('admin.common.back') }}</a>
        <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">{{ $submitLabel }}</button>
    </div>
</form>
