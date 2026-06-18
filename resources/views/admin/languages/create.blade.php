@extends('layouts.admin')

@section('title', __('admin.languages.add_title'))

@section('content')
    @include('admin.languages._form', [
        'action' => route('admin.languages.store'),
        'method' => 'POST',
        'submitLabel' => __('admin.languages.create'),
    ])
@endsection
