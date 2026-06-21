@extends('layouts.admin')
@section('title', __('admin.tax_classes.add_title'))
@section('content')
    @include('admin.tax-classes._form', ['action' => route('admin.tax-classes.store'), 'method' => 'POST', 'submitLabel' => __('admin.tax_classes.create')])
@endsection
