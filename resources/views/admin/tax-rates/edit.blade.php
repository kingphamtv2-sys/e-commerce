@extends('layouts.admin')
@section('title', __('admin.tax_rates.edit_title'))
@section('content')
    @include('admin.tax-rates._form', ['action' => route('admin.tax-rates.update', $taxRate), 'method' => 'PUT', 'submitLabel' => __('admin.tax_rates.save_changes')])
@endsection
