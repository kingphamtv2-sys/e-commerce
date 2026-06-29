@extends('layouts.public')

@section('title', __('storefront.order_success_title').' - '.$siteName)
@section('meta_description', __('storefront.order_success_meta'))

@section('content')
<div class="bg-slate-50">
    <section class="mx-auto max-w-screen-lg px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="rounded-[2rem] border border-emerald-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-600">{{ __('storefront.order_success_eyebrow') }}</p>
                    <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">{{ __('storefront.order_success_title') }}</h1>
                    <p class="mt-2 text-sm font-semibold text-slate-500">{{ __('storefront.order_success_intro') }}</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-extrabold text-emerald-700">{{ $order->order_code }}</span>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">{{ __('storefront.grand_total') }}</p>
                    <p class="mt-1 text-xl font-extrabold text-slate-950">{{ number_format((float) $order->total_amount, $order->currency_decimal_places ?? 0) }} {{ $order->currency_code }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">{{ __('storefront.payment_methods') }}</p>
                    <p class="mt-1 text-sm font-extrabold text-slate-950">{{ $order->payment_method_name }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400">{{ __('storefront.payment_status') }}</p>
                    <p class="mt-1 text-sm font-extrabold uppercase text-amber-700">{{ $order->payment_status }}</p>
                </div>
            </div>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_340px]">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-extrabold text-slate-950">{{ __('storefront.order_items') }}</h2>
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach ($order->orderItems as $item)
                        <div class="flex gap-3 py-4">
                            <div class="h-16 w-16 flex-none overflow-hidden rounded-2xl bg-slate-100">
                                @if ($item->image)
                                    <img src="{{ $item->image }}" alt="{{ $item->product_name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="grid h-full place-items-center text-[10px] font-extrabold uppercase text-slate-400">{{ __('storefront.no_image') }}</div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-extrabold text-slate-950">{{ $item->product_name }}</p>
                                @if ($item->variant_name)
                                    <p class="mt-1 text-xs font-bold text-slate-500">{{ $item->variant_name }}</p>
                                @endif
                                <p class="mt-1 text-xs font-bold text-slate-400">{{ $item->sku ?? $item->product_sku }} x {{ $item->quantity }}</p>
                            </div>
                            <p class="text-sm font-extrabold text-slate-950">{{ number_format((float) $item->total, $order->currency_decimal_places ?? 0) }} {{ $order->currency_code }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <aside class="h-max rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-extrabold text-slate-950">{{ __('storefront.order_summary') }}</h2>
                <div class="mt-5 space-y-3 text-sm font-bold text-slate-600">
                    <div class="flex justify-between"><span>{{ __('storefront.subtotal') }}</span><span>{{ number_format((float) $order->subtotal, $order->currency_decimal_places ?? 0) }} {{ $order->currency_code }}</span></div>
                    <div class="flex justify-between text-emerald-700"><span>{{ __('storefront.discount') }}</span><span>-{{ number_format((float) $order->discount_amount, $order->currency_decimal_places ?? 0) }} {{ $order->currency_code }}</span></div>
                    <div class="flex justify-between"><span>{{ __('storefront.tax') }}</span><span>{{ number_format((float) $order->tax_amount, $order->currency_decimal_places ?? 0) }} {{ $order->currency_code }}</span></div>
                    <div class="flex justify-between"><span>{{ __('storefront.shipping') }}</span><span>{{ number_format((float) $order->shipping_fee, $order->currency_decimal_places ?? 0) }} {{ $order->currency_code }}</span></div>
                    <div class="flex justify-between border-t border-slate-100 pt-4 text-base text-slate-950"><span>{{ __('storefront.grand_total') }}</span><span>{{ number_format((float) $order->total_amount, $order->currency_decimal_places ?? 0) }} {{ $order->currency_code }}</span></div>
                </div>

                @if ($shipping = $order->orderAddresses->firstWhere('type', 'shipping'))
                    <div class="mt-6 rounded-2xl bg-slate-50 p-4 text-sm font-semibold leading-6 text-slate-600">
                        <p class="font-extrabold text-slate-950">{{ __('storefront.shipping_address') }}</p>
                        <p class="mt-2">{{ $shipping->full_name }} - {{ $shipping->phone }}</p>
                        <p>{{ $shipping->address_line }}, {{ $shipping->ward }}, {{ $shipping->district }}, {{ $shipping->province }}, {{ $shipping->country_code }}</p>
                    </div>
                @endif
                <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm font-semibold leading-6 text-slate-600">
                    <p class="font-extrabold text-slate-950">{{ __('storefront.shipping_methods') }}</p>
                    <p class="mt-2">{{ $order->shipping_method_name ?: '—' }}</p>
                    @if($order->shipping_zone_name)<p>{{ $order->shipping_zone_name }}</p>@endif
                    @if($order->shipping_estimated_delivery)<p>{{ $order->shipping_estimated_delivery }}</p>@endif
                </div>
            </aside>
        </div>
    </section>
</div>
@endsection
