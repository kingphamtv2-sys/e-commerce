@extends('emails.layout')
@section('content')
    <p style="font-size:16px;font-weight:700">{{ __('emails.greeting', ['name' => $order->customer_name]) }}</p>
    <p style="line-height:1.6">{{ __('emails.order_created_intro') }} {{ __('emails.thanks', ['store' => $storeName]) }}</p>
    @include('emails._order-summary')
    @if($order->payment_method === 'cod')<p style="padding:12px;background:#fffbeb;border-radius:10px">{{ __('emails.cod_note') }}</p>@endif
    <p><a href="{{ $customerUrl }}" style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:11px 18px;border-radius:9px;font-weight:700">{{ __('emails.view_order') }}</a></p>
@endsection

