@extends('layouts.admin')

@section('title', __('admin.coupons.edit_title'))

@section('content')
    @include('admin.coupons._form', ['action' => route('admin.coupons.update', $coupon), 'method' => 'PUT', 'submitLabel' => __('admin.coupons.save_changes')])
@endsection
