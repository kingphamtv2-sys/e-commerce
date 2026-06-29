@extends('layouts.admin')
@section('title', __('admin.banners.create_title'))
@section('content')
    @include('admin.banners._form', ['action' => route('admin.banners.store'), 'method' => 'POST', 'submitLabel' => __('admin.banners.create')])
@endsection
