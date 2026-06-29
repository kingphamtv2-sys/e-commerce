@extends('emails.layout')
@section('content')
    <p style="font-size:18px;font-weight:700">{{ __('emails.subjects.test_email', ['store' => $storeName]) }}</p>
    <p style="line-height:1.6">{{ __('emails.test_intro') }}</p>
    <p style="color:#64748b">{{ $payload['tested_at'] ?? now()->toIso8601String() }}</p>
@endsection

