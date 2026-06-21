@extends('layouts.admin')
@section('title', __('admin.tax_rates.add_title'))
@section('content')
    @include('admin.tax-rates._form', ['action' => route('admin.tax-rates.store'), 'method' => 'POST', 'submitLabel' => __('admin.tax_rates.create')])
@endsection
