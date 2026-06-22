@php
    $selectedCategories = old('categories', $coupon->exists ? $coupon->categories->pluck('id')->all() : []);
    $selectedProducts = old('products', $coupon->exists ? $coupon->products->pluck('id')->all() : []);
@endphp

@if (session('success'))
    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">{{ __('admin.coupons.review') }}</div>
@endif

<form method="POST" action="{{ $action }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-slate-950">{{ __('admin.coupons.title') }}</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">{{ __('admin.coupons.code') }} *</span>
                    <input name="code" value="{{ old('code', $coupon->code) }}" class="mt-2 w-full rounded-xl border-slate-300 text-sm font-semibold uppercase shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('code') <span class="mt-1 block text-xs font-semibold text-rose-600">{{ $message }}</span> @enderror
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">{{ __('admin.coupons.name') }}</span>
                    <input name="name" value="{{ old('name', $coupon->name) }}" class="mt-2 w-full rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name') <span class="mt-1 block text-xs font-semibold text-rose-600">{{ $message }}</span> @enderror
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm font-bold text-slate-700">{{ __('admin.coupons.description') }}</span>
                    <textarea name="description" rows="3" class="mt-2 w-full rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $coupon->description) }}</textarea>
                </label>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-slate-950">{{ __('admin.coupons.type') }}</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">{{ __('admin.coupons.type') }} *</span>
                    <select name="discount_type" class="mt-2 w-full rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="percentage" @selected(old('discount_type', $coupon->discount_type) === 'percentage')>{{ __('admin.coupons.percentage') }}</option>
                        <option value="fixed_amount" @selected(old('discount_type', $coupon->discount_type) === 'fixed_amount')>{{ __('admin.coupons.fixed_amount') }}</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">{{ __('admin.coupons.value') }} *</span>
                    <input type="number" step="0.01" min="0" name="discount_value" value="{{ old('discount_value', $coupon->discount_value) }}" class="mt-2 w-full rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('discount_value') <span class="mt-1 block text-xs font-semibold text-rose-600">{{ $message }}</span> @enderror
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">{{ __('admin.coupons.max_discount') }}</span>
                    <input type="number" step="0.01" min="0" name="max_discount_amount" value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}" class="mt-2 w-full rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">{{ __('admin.coupons.min_order') }}</span>
                    <input type="number" step="0.01" min="0" name="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" class="mt-2 w-full rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-slate-950">{{ __('admin.coupons.restrictions') }}</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">{{ __('admin.coupons.categories') }}</span>
                    <select name="categories[]" multiple class="mt-2 min-h-44 w-full rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(in_array($category->id, $selectedCategories))>{{ $categoryService->name($category) }}</option>
                        @endforeach
                    </select>
                    <span class="mt-1 block text-xs font-semibold text-slate-500">{{ __('admin.coupons.all_categories') }}</span>
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">{{ __('admin.coupons.products') }}</span>
                    <select name="products[]" multiple class="mt-2 min-h-44 w-full rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(in_array($product->id, $selectedProducts))>{{ $productService->name($product) }} — {{ $product->sku }}</option>
                        @endforeach
                    </select>
                    <span class="mt-1 block text-xs font-semibold text-slate-500">{{ __('admin.coupons.all_products') }}</span>
                </label>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-slate-950">{{ __('admin.coupons.status') }}</h2>
            <div class="mt-5 space-y-5">
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">{{ __('admin.coupons.status') }}</span>
                    <select name="status" class="mt-2 w-full rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="active" @selected(old('status', $coupon->status) === 'active')>{{ __('admin.common.active') }}</option>
                        <option value="inactive" @selected(old('status', $coupon->status) === 'inactive')>{{ __('admin.common.inactive') }}</option>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">{{ __('admin.coupons.usage_limit') }}</span>
                    <input type="number" min="1" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" class="mt-2 w-full rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">{{ __('admin.coupons.usage_limit_per_user') }}</span>
                    <input type="number" min="1" name="usage_limit_per_user" value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}" class="mt-2 w-full rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">{{ __('admin.coupons.starts_at') }}</span>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}" class="mt-2 w-full rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label class="block">
                    <span class="text-sm font-bold text-slate-700">{{ __('admin.coupons.ends_at') }}</span>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $coupon->ends_at?->format('Y-m-d\TH:i')) }}" class="mt-2 w-full rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
            </div>
        </section>

        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <button class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-extrabold text-white shadow-sm hover:bg-indigo-700">{{ $submitLabel }}</button>
            <a href="{{ route('admin.coupons.index') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-center text-sm font-extrabold text-slate-700 hover:bg-slate-50">{{ __('admin.common.back') }}</a>
        </div>
    </aside>
</form>
