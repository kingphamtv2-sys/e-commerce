@extends('layouts.admin')
@section('title', __('admin.currencies.add_title'))
@section('content')
    @include('admin.currencies._form', ['action' => route('admin.currencies.store'), 'method' => 'POST', 'submitLabel' => __('admin.currencies.create')])
@endsection
