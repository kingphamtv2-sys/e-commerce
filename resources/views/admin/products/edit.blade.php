@extends('layouts.admin')
@section('title', __('admin.products.edit_title'))
@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">{{ session('success') }}</div>
    @endif
    @include('admin.products._form', ['action' => route('admin.products.update', $product), 'method' => 'PUT', 'submitLabel' => __('admin.products.save_changes')])
    @include('admin.products._images')
@endsection
