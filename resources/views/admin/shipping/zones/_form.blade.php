@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
        <p class="font-bold">{{ __('admin.common.review_errors') }}</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

@if (session('success'))
    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ $action }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    @php($inputClass = 'mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500')

    <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
        <h2 class="font-bold text-slate-950">{{ __('admin.shipping.zones.details') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('admin.shipping.zones.details_desc') }}</p>
    </div>

    <div class="grid gap-6 p-6 md:grid-cols-2">
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.zones.name') }} *</label>
            <input name="name" value="{{ old('name', $zone->name) }}" required class="{{ $inputClass }}">
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.zones.code') }}</label>
            <input name="code" value="{{ old('code', $zone->code) }}" class="{{ $inputClass }} uppercase">
        </div>
        <div class="md:col-span-2">
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.zones.description') }}</label>
            <textarea name="description" rows="3" class="{{ $inputClass }}">{{ old('description', $zone->description) }}</textarea>
        </div>
        @foreach (['countries', 'cities', 'districts'] as $field)
            <div>
                <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.zones.'.$field) }}</label>
                <textarea name="{{ $field }}" rows="5" class="{{ $inputClass }}" placeholder="{{ __('admin.shipping.zones.lines_help') }}">{{ old($field, implode("\n", $zone->{$field} ?? [])) }}</textarea>
            </div>
        @endforeach
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.sort_order') }}</label>
            <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $zone->sort_order ?? 0) }}" class="{{ $inputClass }}">
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.status') }}</label>
            <select name="status" class="{{ $inputClass }}">
                <option value="active" @selected(old('status', $zone->status) === 'active')>{{ __('admin.common.active') }}</option>
                <option value="inactive" @selected(old('status', $zone->status) === 'inactive')>{{ __('admin.common.inactive') }}</option>
            </select>
        </div>
    </div>

    <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50/70 px-6 py-4">
        <a href="{{ route('admin.shipping.zones.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">{{ __('admin.common.cancel') }}</a>
        <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">{{ $submitLabel }}</button>
    </div>
</form>
