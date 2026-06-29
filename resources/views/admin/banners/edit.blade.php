@extends('layouts.admin')
@section('title', __('admin.banners.edit_title'))
@section('content')
    @if(session('success'))<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
    @include('admin.banners._form', ['action' => route('admin.banners.update', $banner), 'method' => 'PUT', 'submitLabel' => __('admin.banners.save_changes')])
@endsection
