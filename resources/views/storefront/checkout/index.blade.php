@extends('layouts.public')

@section('title', __('storefront.checkout_title').' — '.$siteName)
@section('meta_description', __('storefront.checkout_meta'))

@section('content')
<div class="bg-slate-50">
    <section class="mx-auto max-w-screen-2xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-indigo-600">{{ __('storefront.checkout_eyebrow') }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">{{ __('storefront.checkout_title') }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ __('storefront.checkout_intro') }}</p>
            </div>
            <a href="{{ route('cart.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-extrabold text-slate-700 hover:bg-slate-100">{{ __('storefront.back_to_cart') }}</a>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-bold text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('checkout.store') }}" method="POST" data-checkout-form data-checkout-summary-url="{{ route('checkout.summary') }}" data-shipping-methods-url="{{ route('checkout.shipping.methods') }}" data-shipping-select-url="{{ route('checkout.shipping.select') }}" class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_400px]" x-data="{ billingSame: @js((bool) old('billing_same_as_shipping', true)) }">
            @csrf
            <input type="hidden" name="shipping_method_id" value="{{ old('shipping_method_id', $checkoutSummary['selected_shipping_method']['id'] ?? '') }}" data-selected-shipping-method>
            <div class="space-y-6">
                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-extrabold text-slate-950">{{ __('storefront.contact_information') }}</h2>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.full_name') }}</span>
                            <input name="contact[name]" value="{{ old('contact.name', auth()->user()?->name) }}" required class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.email') }}</span>
                            <input type="email" name="contact[email]" value="{{ old('contact.email', auth()->user()?->email) }}" required class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.phone') }}</span>
                            <input name="contact[phone]" value="{{ old('contact.phone', auth()->user()?->phone) }}" required class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-extrabold text-slate-950">{{ __('storefront.shipping_address') }}</h2>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.full_name') }}</span>
                            <input name="shipping[full_name]" value="{{ old('shipping.full_name', auth()->user()?->name) }}" required class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.phone') }}</span>
                            <input name="shipping[phone]" value="{{ old('shipping.phone', auth()->user()?->phone) }}" required class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.country_code') }}</span>
                            <input name="shipping[country_code]" value="{{ old('shipping.country_code', 'VN') }}" required maxlength="10" data-checkout-summary-input class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold uppercase shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.province') }}</span>
                            <input name="shipping[province]" value="{{ old('shipping.province') }}" required data-checkout-summary-input class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.district') }}</span>
                            <input name="shipping[district]" value="{{ old('shipping.district') }}" data-checkout-summary-input class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.ward') }}</span>
                            <input name="shipping[ward]" value="{{ old('shipping.ward') }}" class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.address_line') }}</span>
                            <input name="shipping[address_line]" value="{{ old('shipping.address_line') }}" required class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-950">{{ __('storefront.shipping_methods') }}</h2>
                            <p class="mt-1 text-sm font-semibold text-slate-500">{{ __('storefront.shipping_methods_intro') }}</p>
                        </div>
                        <span data-shipping-loading class="hidden rounded-full bg-slate-100 px-3 py-1 text-xs font-extrabold text-slate-500">{{ __('storefront.loading') }}</span>
                    </div>
                    <div data-shipping-methods data-empty-message="{{ __('storefront.shipping_no_methods') }}" class="mt-5 space-y-3">
                        @forelse ($checkoutSummary['available_shipping_methods'] as $method)
                            <label @class(['block cursor-pointer rounded-2xl border p-5 transition', 'border-indigo-300 bg-indigo-50/50' => ($checkoutSummary['selected_shipping_method']['id'] ?? null) === $method['id'], 'border-slate-200 bg-slate-50 hover:border-indigo-200' => ($checkoutSummary['selected_shipping_method']['id'] ?? null) !== $method['id']])>
                                <span class="flex items-start gap-4">
                                    <input type="radio" name="_shipping_method_radio" value="{{ $method['id'] }}" @checked(($checkoutSummary['selected_shipping_method']['id'] ?? null) === $method['id']) data-shipping-method-option class="mt-1 h-5 w-5 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center justify-between gap-2">
                                            <span class="text-base font-extrabold text-slate-950">{{ $method['name'] }}</span>
                                            <span class="font-extrabold text-slate-950">{{ $method['formatted_shipping_amount'] }}</span>
                                        </span>
                                        @if($method['description'])<span class="mt-1 block text-sm font-semibold leading-6 text-slate-600">{{ $method['description'] }}</span>@endif
                                        @if($method['estimated_delivery'])<span class="mt-2 block text-xs font-bold text-slate-500">{{ $method['estimated_delivery'] }}</span>@endif
                                    </span>
                                </span>
                            </label>
                        @empty
                            <p class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">{{ __('storefront.shipping_no_methods') }}</p>
                        @endforelse
                    </div>
                    <p data-shipping-errors @class(['mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700', 'hidden' => $checkoutSummary['has_available_shipping_methods']])>{{ __('storefront.shipping_no_methods') }}</p>
                </section>

                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-lg font-extrabold text-slate-950">{{ __('storefront.billing_address') }}</h2>
                        <label class="inline-flex items-center gap-2 text-sm font-extrabold text-slate-700">
                            <input type="checkbox" name="billing_same_as_shipping" value="1" @checked((bool) old('billing_same_as_shipping', true)) x-model="billingSame" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('storefront.billing_same_as_shipping') }}
                        </label>
                    </div>
                    <div x-show="!billingSame" x-cloak class="mt-5 grid gap-4 sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.full_name') }}</span>
                            <input name="billing[full_name]" value="{{ old('billing.full_name') }}" class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.phone') }}</span>
                            <input name="billing[phone]" value="{{ old('billing.phone') }}" class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.country_code') }}</span>
                            <input name="billing[country_code]" value="{{ old('billing.country_code', 'VN') }}" maxlength="10" class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold uppercase shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.province') }}</span>
                            <input name="billing[province]" value="{{ old('billing.province') }}" class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.district') }}</span>
                            <input name="billing[district]" value="{{ old('billing.district') }}" class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.ward') }}</span>
                            <input name="billing[ward]" value="{{ old('billing.ward') }}" class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="text-sm font-bold text-slate-700">{{ __('storefront.address_line') }}</span>
                            <input name="billing[address_line]" value="{{ old('billing.address_line') }}" class="mt-1 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <label class="block">
                        <span class="text-lg font-extrabold text-slate-950">{{ __('storefront.order_note') }}</span>
                        <textarea name="note" rows="4" class="mt-3 w-full rounded-2xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('note') }}</textarea>
                    </label>
                </section>
            </div>

            <aside class="h-max rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-28">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-extrabold text-slate-950">{{ __('storefront.checkout_summary') }}</h2>
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-extrabold text-indigo-700">{{ $currentCurrency->code }}</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($cartItems as $item)
                        <div class="flex gap-3 py-4">
                            <div class="h-16 w-16 flex-none overflow-hidden rounded-2xl bg-slate-100">
                                @if ($item['image_url'])
                                    <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                                @else
                                    <div class="grid h-full place-items-center text-[10px] font-extrabold uppercase text-slate-400">{{ __('storefront.no_image') }}</div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-extrabold text-slate-950">{{ $item['name'] }}</p>
                                @if ($item['variant_label'])
                                    <p class="mt-1 text-xs font-bold text-slate-500">{{ $item['variant_label'] }}</p>
                                @endif
                                <p class="mt-1 text-xs font-bold text-slate-400">{{ $item['sku'] }} × {{ $item['quantity'] }}</p>
                            </div>
                            <p class="text-sm font-extrabold text-slate-950">{{ $item['formatted_subtotal'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="space-y-3 border-t border-slate-100 pt-5 text-sm font-bold text-slate-600">
                    <div class="flex items-center justify-between">
                        <span>{{ __('storefront.subtotal') }}</span>
                        <span data-checkout-subtotal class="text-slate-950">{{ $checkoutSummary['formatted']['subtotal'] }}</span>
                    </div>
                    <div class="flex items-center justify-between text-emerald-700">
                        <span>{{ __('storefront.discount') }}</span>
                        <span>-<span data-checkout-discount>{{ $checkoutSummary['formatted']['discount_amount'] }}</span></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>{{ __('storefront.tax') }}</span>
                        <span data-checkout-tax class="text-slate-950">{{ $checkoutSummary['formatted']['tax_amount'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>{{ __('storefront.shipping') }}</span>
                        <span data-checkout-shipping class="text-slate-950">{{ $checkoutSummary['formatted']['shipping_amount'] }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100 pt-4 text-base text-slate-950">
                        <span>{{ __('storefront.grand_total') }}</span>
                        <span data-checkout-grand-total class="text-2xl font-extrabold">{{ $checkoutSummary['formatted']['grand_total'] }}</span>
                    </div>
                </div>
                <p data-checkout-errors class="mt-4 hidden rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700"></p>
                <p data-checkout-success class="mt-4 hidden rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-800"></p>
                <button type="submit" data-checkout-submit @disabled(! $checkoutSummary['selected_shipping_method']) class="mt-5 flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3.5 text-sm font-extrabold text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-400">
                    <span data-checkout-submit-label>{{ __('storefront.create_checkout_session') }}</span>
                </button>
                <p class="mt-3 text-xs font-semibold leading-5 text-slate-500">{{ __('storefront.checkout_task17_notice') }}</p>
            </aside>
        </form>
    </section>
</div>
@endsection
