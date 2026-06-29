@extends('layouts.admin')

@section('title', __('admin.shipping.zones.create'))
@section('page-actions')<a href="{{ route('admin.shipping.zones.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">{{ __('admin.common.back') }}</a>@endsection

@section('content')
    @include('admin.shipping.zones._form', [
        'action' => route('admin.shipping.zones.store'),
        'method' => 'POST',
        'submitLabel' => __('admin.shipping.zones.create'),
    ])
@endsection
