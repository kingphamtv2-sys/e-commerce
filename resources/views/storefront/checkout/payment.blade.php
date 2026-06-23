@extends('layouts.public')

@section('title', __('storefront.payment_title').' - '.$siteName)
@section('meta_description', __('storefront.payment_meta'))

@section('content')
<div class="bg-slate-50">
    <section class="mx-auto max-w-screen-lg px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-indigo-600">{{ __('storefront.payment_eyebrow') }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">{{ __('storefront.payment_title') }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ __('storefront.payment_intro') }}</p>
            </div>
            <a href="{{ route('checkout.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-extrabold text-slate-700 hover:bg-slate-100">{{ __('storefront.back_to_checkout') }}</a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-bold text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-6">
                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-extrabold text-slate-950">{{ __('storefront.payment_methods') }}</h2>

                    @if ($cod['enabled'])
                        <form method="POST" action="{{ route('checkout.payment.cod', $checkoutSession->token) }}" data-cod-payment-form class="mt-5">
                            @csrf
                            <label class="block rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <span class="flex items-start gap-4">
                                    <input type="radio" checked class="mt-1 h-5 w-5 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-base font-extrabold text-slate-950">{{ $cod['display_name'] }}</span>
                                        @if ($cod['description'])
                                            <span class="mt-1 block text-sm font-semibold leading-6 text-slate-600">{{ $cod['description'] }}</span>
                                        @endif
                                        @if ($cod['instruction'])
                                            <span class="mt-4 block rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold leading-6 text-amber-900">{{ $cod['instruction'] }}</span>
                                        @endif
                                    </span>
                                </span>
                            </label>

                            @if (! $codAvailable)
                                <p class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">{{ $codUnavailableMessage }}</p>
                            @endif

                            <p data-cod-payment-errors class="mt-4 hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"></p>
                            <p data-cod-payment-success class="mt-4 hidden rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800"></p>

                            <button type="submit" @disabled(! $codAvailable) class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3.5 text-sm font-extrabold text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-400">
                                <span data-cod-payment-submit-label data-loading-label="{{ __('storefront.payment_selecting') }}">{{ __('storefront.payment_select_cod') }}</span>
                            </button>
                        </form>
                    @else
                        <p class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">{{ __('storefront.payment_cod_disabled') }}</p>
                    @endif
                </section>

                <section data-place-order-panel @class(['rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm', 'hidden' => ! $checkoutSession->payment_method_code])>
                    <h2 class="text-lg font-extrabold text-slate-950">{{ __('storefront.place_order_title') }}</h2>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-500">{{ __('storefront.place_order_intro') }}</p>
                    <form method="POST" action="{{ route('checkout.order.store', $checkoutSession->token) }}" data-place-order-form class="mt-5">
                        @csrf
                        <p data-place-order-errors class="mb-4 hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"></p>
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-indigo-600 px-5 py-3.5 text-sm font-extrabold text-white hover:bg-indigo-700 disabled:cursor-wait disabled:bg-indigo-300">
                            <span data-place-order-submit-label data-loading-label="{{ __('storefront.placing_order') }}">{{ __('storefront.place_order') }}</span>
                        </button>
                    </form>
                </section>
            </div>

            <aside class="h-max rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-28">
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-extrabold text-slate-950">{{ __('storefront.payment_summary') }}</h2>
                    <p class="mt-1 text-xs font-bold text-slate-500">{{ __('storefront.checkout_session') }} #{{ $checkoutSession->id }}</p>
                </div>
                <div class="space-y-3 pt-5 text-sm font-bold text-slate-600">
                    <div class="flex items-center justify-between">
                        <span>{{ __('storefront.grand_total') }}</span>
                        <span class="text-xl font-extrabold text-slate-950">{{ $formattedPaymentAmount }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>{{ __('storefront.payment_status') }}</span>
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-extrabold uppercase text-amber-700">{{ $checkoutSession->payment_status ?: __('storefront.payment_not_selected') }}</span>
                    </div>
                </div>
                <p class="mt-5 rounded-xl bg-slate-50 px-4 py-3 text-xs font-semibold leading-5 text-slate-500">{{ __('storefront.payment_task19_notice') }}</p>
            </aside>
        </div>
    </section>
</div>
@endsection
