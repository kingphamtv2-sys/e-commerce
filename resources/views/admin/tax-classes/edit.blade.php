@extends('layouts.admin')
@section('title', __('admin.tax_classes.edit_title'))
@section('content')
    @include('admin.tax-classes._form', ['action' => route('admin.tax-classes.update', $taxClass), 'method' => 'PUT', 'submitLabel' => __('admin.tax_classes.save_changes')])
@endsection
