@extends('layouts.admin')

@section('title', __('admin.languages.edit_title'))

@section('content')
    @include('admin.languages._form', [
        'action' => route('admin.languages.update', $language),
        'method' => 'PUT',
        'submitLabel' => __('admin.languages.save_changes'),
    ])
@endsection
