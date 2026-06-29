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
    @if ($methodVerb !== 'POST') @method($methodVerb) @endif
    @php($inputClass = 'mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500')

    <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
        <h2 class="font-bold text-slate-950">{{ __('admin.shipping.methods.details') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('admin.shipping.methods.details_desc') }}</p>
    </div>

    <div class="grid gap-6 p-6 md:grid-cols-2">
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.methods.zone') }}</label>
            <select name="shipping_zone_id" class="{{ $inputClass }}">
                <option value="">{{ __('admin.shipping.methods.global_zone') }}</option>
                @foreach ($zones as $zone)
                    <option value="{{ $zone->id }}" @selected((string) old('shipping_zone_id', $method->shipping_zone_id) === (string) $zone->id)>{{ $zone->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.methods.type') }} *</label>
            <select name="type" class="{{ $inputClass }}" required>
                <option value="flat_rate" @selected(old('type', $method->type) === 'flat_rate')>{{ __('admin.shipping.methods.flat_rate') }}</option>
                <option value="free_shipping" @selected(old('type', $method->type) === 'free_shipping')>{{ __('admin.shipping.methods.free_shipping') }}</option>
                <option value="pickup" @selected(old('type', $method->type) === 'pickup')>{{ __('admin.shipping.methods.pickup') }}</option>
            </select>
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.methods.name') }} *</label>
            <input name="name" value="{{ old('name', $method->name) }}" required class="{{ $inputClass }}">
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.methods.code') }} *</label>
            <input name="code" value="{{ old('code', $method->code) }}" required class="{{ $inputClass }}">
        </div>
        <div class="md:col-span-2">
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.methods.description') }}</label>
            <textarea name="description" rows="3" class="{{ $inputClass }}">{{ old('description', $method->description) }}</textarea>
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.methods.base_fee') }}</label>
            <input type="number" min="0" step="0.01" name="base_fee" value="{{ old('base_fee', $method->base_fee ?? 0) }}" class="{{ $inputClass }}">
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.methods.free_threshold') }}</label>
            <input type="number" min="0" step="0.01" name="free_shipping_min_amount" value="{{ old('free_shipping_min_amount', $method->free_shipping_min_amount) }}" class="{{ $inputClass }}">
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.methods.min_order') }}</label>
            <input type="number" min="0" step="0.01" name="min_order_amount" value="{{ old('min_order_amount', $method->min_order_amount) }}" class="{{ $inputClass }}">
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.methods.max_order') }}</label>
            <input type="number" min="0" step="0.01" name="max_order_amount" value="{{ old('max_order_amount', $method->max_order_amount) }}" class="{{ $inputClass }}">
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.methods.estimate_min') }}</label>
            <input type="number" min="0" name="estimated_delivery_min_days" value="{{ old('estimated_delivery_min_days', $method->estimated_delivery_min_days) }}" class="{{ $inputClass }}">
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.methods.estimate_max') }}</label>
            <input type="number" min="0" name="estimated_delivery_max_days" value="{{ old('estimated_delivery_max_days', $method->estimated_delivery_max_days) }}" class="{{ $inputClass }}">
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.sort_order') }}</label>
            <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $method->sort_order ?? 0) }}" class="{{ $inputClass }}">
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-800">{{ __('admin.shipping.status') }}</label>
            <select name="status" class="{{ $inputClass }}">
                <option value="active" @selected(old('status', $method->status) === 'active')>{{ __('admin.common.active') }}</option>
                <option value="inactive" @selected(old('status', $method->status) === 'inactive')>{{ __('admin.common.inactive') }}</option>
            </select>
        </div>
    </div>

    <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50/70 px-6 py-4">
        <a href="{{ route('admin.shipping.methods.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">{{ __('admin.common.cancel') }}</a>
        <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">{{ $submitLabel }}</button>
    </div>
</form>
