@extends('layouts.admin')
@section('title', __('admin.products.edit_title'))
@section('content')
    @php
        $errorKeys = collect($errors->keys());
        $errorTabs = collect([
            'general' => $errorKeys->contains(fn ($key) => !str_starts_with($key, 'translations.') && !in_array($key, ['option', 'option_value', 'variant', 'product_image', 'variant_image'])),
            'translations' => $errorKeys->contains(fn ($key) => str_starts_with($key, 'translations.') && !str_contains($key, 'meta_')),
            'seo' => $errorKeys->contains(fn ($key) => str_contains($key, 'meta_')),
            'options' => $errors->has('option') || $errors->has('option_value'),
            'variants' => $errors->has('variant'),
            'images' => $errors->has('product_image'),
            'variant-images' => $errors->has('variant_image'),
        ])->filter()->keys()->values()->all();
        $initialTab = $errorTabs[0] ?? 'general';
    @endphp
    @if (session('success'))<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">{{ session('success') }}</div>@endif
    <form id="product-delete-form" method="POST" action="{{ route('admin.products.destroy', $product) }}" class="hidden">@csrf @method('DELETE')</form>
    <div x-data="productTabs(@js($initialTab), @js($errorTabs))" @input.capture="markDirty($event)" @change.capture="markDirty($event)" @admin:tab-saved.window="markSaved($event.detail.tab)" @admin:tab-error.window="markError($event.detail.tab)">
        @include('admin.products._tabs', ['isEdit' => true])
        @include('admin.products._form', ['action' => route('admin.products.update', $product), 'method' => 'PUT', 'submitLabel' => __('admin.products.save_changes')])
        @include('admin.products._images')
        @include('admin.products._options-variants')
        @include('admin.products._variant_images_tab')
        @include('admin.products._inventory_tab')
    </div>
    @include('admin.products.partials.delete-modal')
@endsection
