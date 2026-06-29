@extends('layouts.admin')
@section('title', __('admin.products.add_title'))
@section('content')
    @php
        $errorKeys = collect($errors->keys());
        $errorTabs = collect([
            'general' => $errorKeys->contains(fn ($key) => !str_starts_with($key, 'translations.')),
            'translations' => $errorKeys->contains(fn ($key) => str_starts_with($key, 'translations.') && !str_contains($key, 'meta_')),
            'seo' => $errorKeys->contains(fn ($key) => str_contains($key, 'meta_')),
        ])->filter()->keys()->values()->all();
        $initialTab = $errorTabs[0] ?? 'general';
    @endphp
    <div x-data="productTabs(@js($initialTab), @js($errorTabs))" @input.capture="markDirty($event)" @change.capture="markDirty($event)">
        @include('admin.products._tabs', ['isEdit' => false])
        @include('admin.products._form', ['action' => route('admin.products.store'), 'method' => 'POST', 'submitLabel' => __('admin.products.create')])
    </div>
@endsection
