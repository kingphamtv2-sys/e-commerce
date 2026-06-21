@extends('layouts.admin')
@section('title', __('admin.categories.add_title'))
@section('content')
    @include('admin.categories._form', ['action' => route('admin.categories.store'), 'method' => 'POST', 'submitLabel' => __('admin.categories.create')])
@endsection
