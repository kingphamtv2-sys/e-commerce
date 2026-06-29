@extends('layouts.admin')
@section('title', __('admin.categories.edit_title'))
@section('content')
    @include('admin.categories._form', ['action' => route('admin.categories.update', $category), 'method' => 'PUT', 'submitLabel' => __('admin.categories.save_changes')])
@endsection
