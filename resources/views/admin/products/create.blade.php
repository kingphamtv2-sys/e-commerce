@extends('layouts.admin')
@section('title', __('admin.products.add_title'))
@section('content')
    @include('admin.products._form', ['action' => route('admin.products.store'), 'method' => 'POST', 'submitLabel' => __('admin.products.create')])
@endsection
