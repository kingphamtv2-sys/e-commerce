@extends('emails.layout')
@section('content')
    <p style="font-size:16px;font-weight:700">{{ __('emails.greeting', ['name' => $order->customer_name]) }}</p>
    <p style="line-height:1.6">{{ __('emails.payment_failed_intro') }}</p>
    @include('emails._order-summary')
    <p><a href="{{ $customerUrl }}" style="display:inline-block;background:#b45309;color:#fff;text-decoration:none;padding:11px 18px;border-radius:9px;font-weight:700">{{ __('emails.view_order') }}</a></p>
@endsection

