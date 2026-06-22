@extends('layouts.admin')

@section('title', __('admin.coupons.add_title'))

@section('content')
    @include('admin.coupons._form', ['action' => route('admin.coupons.store'), 'method' => 'POST', 'submitLabel' => __('admin.coupons.create')])
@endsection
