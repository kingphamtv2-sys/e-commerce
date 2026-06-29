<div class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-indigo-600">{{ __('account.order_detail') }}</p>
                <h1 class="mt-2 text-2xl font-extrabold text-slate-950">{{ $order->order_code }}</h1>
                <p class="mt-1 text-sm font-semibold text-slate-400">{{ ($order->placed_at ?? $order->created_at)->format('Y-m-d H:i') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @include('account.partials.status', ['status' => $order->order_status])
                @include('account.partials.status', ['status' => $order->payment_status])
                @include('account.partials.status', ['status' => $order->fulfillment_status])
            </div>
        </div>
    </section>

    @php($paymentSnapshot = $order->orderPayments->first())

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-lg font-extrabold text-slate-950">{{ __('account.order_items') }}</h2>
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach($order->orderItems as $item)
                        <div class="flex gap-4 py-4">
                            <div class="h-16 w-16 flex-none overflow-hidden rounded-2xl bg-slate-100">
                                @if($item->image)<img src="{{ $item->image }}" alt="{{ $item->product_name }}" class="h-full w-full object-cover">@endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-extrabold text-slate-950">{{ $item->product_name }}</p>
                                @if($item->variant_name)<p class="mt-1 text-xs font-bold text-slate-500">{{ $item->variant_name }}</p>@endif
                                <p class="mt-1 text-xs font-semibold text-slate-400">{{ $item->sku ?: $item->product_sku }} · {{ __('account.quantity') }} {{ $item->quantity }}</p>
                                <p class="mt-2 text-xs font-bold text-slate-500">{{ $money($item->price) }} × {{ $item->quantity }}</p>
                            </div>
                            <p class="text-sm font-extrabold text-slate-950">{{ $money($item->total) }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-extrabold text-slate-950">{{ __('account.customer_information') }}</h2>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-3">
                    <div>
                        <dt class="text-slate-400">{{ __('account.name') }}</dt>
                        <dd class="mt-1 font-bold text-slate-800">{{ $order->customer_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">{{ __('account.email') }}</dt>
                        <dd class="mt-1 break-all font-bold text-slate-800">{{ $order->customer_email ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">{{ __('account.phone') }}</dt>
                        <dd class="mt-1 font-bold text-slate-800">{{ $order->customer_phone }}</dd>
                    </div>
                </dl>
            </section>

            <div class="grid gap-5 md:grid-cols-2">
                @foreach(['shipping' => __('account.shipping_address'), 'billing' => __('account.billing_address')] as $type => $label)
                    @php($address = $order->orderAddresses->firstWhere('type', $type))
                    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="font-extrabold text-slate-950">{{ $label }}</h2>
                        @if($address)
                            <p class="mt-3 text-sm font-bold text-slate-800">{{ $address->full_name }} · {{ $address->phone }}</p>
                            <p class="mt-1 text-sm leading-6 text-slate-500">{{ collect([$address->address_line, $address->ward, $address->district, $address->province, $address->country_code])->filter()->implode(', ') }}</p>
                        @else
                            <p class="mt-3 text-sm text-slate-400">—</p>
                        @endif
                    </section>
                @endforeach
            </div>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-extrabold text-slate-950">{{ __('account.shipping_information') }}</h2>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-400">{{ __('account.shipping_method') }}</dt><dd class="mt-1 font-bold text-slate-800">{{ $order->shipping_method_name ?: '—' }}</dd></div>
                    <div><dt class="text-slate-400">{{ __('account.shipping_zone') }}</dt><dd class="mt-1 font-bold text-slate-800">{{ $order->shipping_zone_name ?: '—' }}</dd></div>
                    <div><dt class="text-slate-400">{{ __('account.shipping') }}</dt><dd class="mt-1 font-bold text-slate-800">{{ $money($order->shipping_fee) }}</dd></div>
                    <div><dt class="text-slate-400">{{ __('account.shipping_estimated_delivery') }}</dt><dd class="mt-1 font-bold text-slate-800">{{ $order->shipping_estimated_delivery ?: '—' }}</dd></div>
                </dl>
            </section>

            @if($order->statusHistories->isNotEmpty())
                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-extrabold text-slate-950">{{ __('account.timeline') }}</h2>
                    <ol class="mt-4 space-y-4">
                        @foreach($order->statusHistories as $history)
                            <li class="flex gap-3">
                                <span class="mt-1.5 h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
                                <div>
                                    <p class="text-sm font-extrabold text-slate-800">{{ __('account.statuses.'.$history->to_status) }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $history->created_at->format('Y-m-d H:i') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif
        </div>

        <aside class="space-y-5">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-extrabold text-slate-950">{{ __('account.payment_information') }}</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="text-slate-400">{{ __('account.payment_method') }}</dt><dd class="mt-1 font-extrabold text-slate-800">{{ $order->payment_method_name ?: strtoupper($order->payment_method) }}</dd></div>
                    <div><dt class="text-slate-400">{{ __('account.payment_status') }}</dt><dd class="mt-1">@include('account.partials.status', ['status' => $order->payment_status])</dd></div>
                    <div><dt class="text-slate-400">{{ __('account.payment_amount') }}</dt><dd class="mt-1 font-extrabold text-slate-800">{{ $money($paymentSnapshot?->amount ?? $order->total_amount) }}</dd></div>
                    @if($transaction = $order->paymentTransactions->first())
                        <div><dt class="text-slate-400">{{ __('account.transaction_number') }}</dt><dd class="mt-1 break-all font-bold text-slate-800">{{ $transaction->transaction_number }}</dd></div>
                    @endif
                    @if($order->paid_at)<div><dt class="text-slate-400">{{ __('account.paid_at') }}</dt><dd class="mt-1 font-bold text-slate-800">{{ $order->paid_at->format('Y-m-d H:i') }}</dd></div>@endif
                </dl>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-extrabold text-slate-950">{{ __('account.order_summary') }}</h2>
                <dl class="mt-4 space-y-3 text-sm font-bold">
                    <div class="flex justify-between text-slate-500"><dt>{{ __('account.subtotal') }}</dt><dd>{{ $money($order->subtotal) }}</dd></div>
                    <div class="flex justify-between text-emerald-700"><dt>{{ __('account.discount') }}</dt><dd>-{{ $money($order->discount_amount) }}</dd></div>
                    <div class="flex justify-between text-slate-500"><dt>{{ __('account.tax') }}</dt><dd>{{ $money($order->tax_amount) }}</dd></div>
                    <div class="flex justify-between text-slate-500"><dt>{{ __('account.shipping') }}</dt><dd>{{ $money($order->shipping_fee) }}</dd></div>
                    <div class="flex justify-between border-t border-slate-100 pt-4 text-base text-slate-950"><dt>{{ __('account.total') }}</dt><dd>{{ $money($order->total_amount) }}</dd></div>
                </dl>
            </section>

            @if(!$guestView && $order->payment_method === 'online' && $order->payment_status !== 'paid' && !in_array($order->order_status, ['cancelled', 'completed'], true))
                <form method="POST" action="{{ route('orders.payment.retry', $order) }}">
                    @csrf
                    <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-extrabold text-white">{{ __('account.retry_payment') }}</button>
                </form>
            @endif
            <a href="{{ $guestView ? route('products.index') : route('account.orders.index') }}" class="block rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-extrabold text-slate-600">{{ $guestView ? __('account.continue_shopping') : __('account.back_to_orders') }}</a>
        </aside>
    </div>
</div>
