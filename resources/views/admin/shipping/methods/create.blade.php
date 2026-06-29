@extends('layouts.admin')

@section('title', __('admin.shipping.methods.create'))
@section('page-actions')<a href="{{ route('admin.shipping.methods.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">{{ __('admin.common.back') }}</a>@endsection

@section('content')
    @include('admin.shipping.methods._form', [
        'action' => route('admin.shipping.methods.store'),
        'methodVerb' => 'POST',
        'submitLabel' => __('admin.shipping.methods.create'),
    ])
@endsection
