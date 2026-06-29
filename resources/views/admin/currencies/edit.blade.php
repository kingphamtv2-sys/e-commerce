@extends('layouts.admin')
@section('title', __('admin.currencies.edit_title'))
@section('content')
    @include('admin.currencies._form', ['action' => route('admin.currencies.update', $currency), 'method' => 'PUT', 'submitLabel' => __('admin.currencies.save_changes')])
@endsection
