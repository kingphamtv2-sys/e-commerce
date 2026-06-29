@extends('emails.layout')
@section('content')
    <p style="font-size:16px;font-weight:700">{{ __('emails.admin_new_order_intro') }}</p>
    <p>{{ __('emails.customer') }}: <strong>{{ $order->customer_name }}</strong><br>
       Email: {{ $order->customer_email }}<br>
       {{ __('emails.phone') }}: {{ $order->customer_phone }}</p>
    @include('emails._order-summary')
    <p><a href="{{ $adminUrl }}" style="display:inline-block;background:#0f172a;color:#fff;text-decoration:none;padding:11px 18px;border-radius:9px;font-weight:700">{{ __('emails.review_order') }}</a></p>
@endsection

