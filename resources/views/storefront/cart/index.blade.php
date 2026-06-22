@extends('layouts.public')

@section('title', __('storefront.cart').' — '.$siteName)
@section('meta_description', __('storefront.cart_meta'))

@section('content')
<div class="bg-slate-50">
    <section class="mx-auto max-w-screen-2xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-indigo-600">{{ __('storefront.cart_eyebrow') }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">{{ __('storefront.cart_title') }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ __('storefront.cart_intro') }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-extrabold text-slate-700 hover:bg-slate-100">{{ __('storefront.continue_shopping') }}</a>
        </div>

        @include('storefront.cart._empty', ['show' => $cartSummary['is_empty']])

        <div data-cart-items @class(['grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px]', 'hidden' => $cartSummary['is_empty']])>
            <div class="space-y-4">
                @foreach ($cartItems as $item)
                    @include('storefront.cart._item', ['item' => $item])
                @endforeach
            </div>

            <aside data-cart-summary-box class="h-max rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-28">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-extrabold text-slate-950">{{ __('storefront.cart_summary') }}</h2>
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-extrabold text-indigo-700"><span data-cart-total-items>{{ $cartSummary['total_items'] }}</span> {{ __('storefront.items') }}</span>
                </div>
                <div class="space-y-4 py-5">
                    <div class="flex items-center justify-between text-sm font-bold text-slate-600">
                        <span>{{ __('storefront.subtotal') }}</span>
                        <span data-cart-subtotal class="text-xl font-extrabold text-slate-950">{{ $cartSummary['formatted_subtotal'] }}</span>
                    </div>
                    <div data-cart-discount-row @class(['flex items-center justify-between text-sm font-bold text-emerald-700', 'hidden' => ($cartSummary['discount_amount'] ?? 0) <= 0])>
                        <span>{{ __('storefront.discount') }}</span>
                        <span>-<span data-cart-discount>{{ $cartSummary['formatted_discount_amount'] }}</span></span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100 pt-4 text-sm font-bold text-slate-700">
                        <span>{{ __('storefront.estimated_total') }}</span>
                        <span data-cart-estimated-total class="text-xl font-extrabold text-slate-950">{{ $cartSummary['formatted_estimated_total'] }}</span>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-extrabold text-slate-950">{{ __('storefront.coupon') }}</p>
                        <form action="{{ route('cart.coupon.apply') }}" method="POST" data-coupon-apply class="mt-3 flex gap-2">
                            @csrf
                            <input name="code" value="" placeholder="{{ __('storefront.coupon_code') }}" class="min-w-0 flex-1 rounded-xl border-slate-300 text-sm font-bold uppercase shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-slate-800">{{ __('storefront.apply_coupon') }}</button>
                        </form>
                        <p data-cart-errors class="mt-3 hidden rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700"></p>
                        <div data-applied-coupon @class(['mt-3 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2', 'hidden' => empty($cartSummary['applied_coupon'])])>
                            <span class="text-xs font-extrabold text-emerald-800">{{ __('storefront.applied_coupon') }}: <span data-applied-coupon-code>{{ $cartSummary['applied_coupon']['code'] ?? '' }}</span></span>
                            <button type="button" data-coupon-remove="{{ route('cart.coupon.remove') }}" class="text-xs font-extrabold text-emerald-800 underline decoration-emerald-300 underline-offset-4 hover:text-emerald-950">{{ __('storefront.remove_coupon') }}</button>
                        </div>
                        <p class="mt-3 text-xs font-semibold leading-5 text-slate-500">{{ __('storefront.coupon_help') }}</p>
                    </div>
                    <p class="rounded-2xl bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-500">{{ __('storefront.checkout_note') }}</p>
                    @if ($cartSummary['has_unavailable'])
                        <p class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">{{ __('storefront.cart_unavailable_checkout') }}</p>
                    @endif
                </div>
                <button type="button" disabled class="flex w-full cursor-not-allowed items-center justify-center rounded-2xl bg-slate-300 px-5 py-3.5 text-sm font-extrabold text-white">{{ __('storefront.proceed_to_checkout') }}</button>
                <button type="button" data-cart-clear="{{ route('cart.clear') }}" data-confirm-message="{{ __('storefront.clear_cart_confirm') }}" class="mt-3 flex w-full items-center justify-center rounded-2xl border border-rose-200 px-5 py-3 text-sm font-extrabold text-rose-700 hover:bg-rose-50">{{ __('storefront.clear_cart') }}</button>
            </aside>
        </div>
    </section>
</div>
@endsection
