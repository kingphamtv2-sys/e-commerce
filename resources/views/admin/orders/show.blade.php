@extends('layouts.admin')
@section('title', __('admin.orders.detail_title', ['order' => $order->order_code]))
@section('page-actions')<a href="{{ route('admin.orders.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">{{ __('admin.orders.back') }}</a>@endsection
@section('content')
    @php
        $shipping = $order->orderAddresses->firstWhere('type', 'shipping');
        $billing = $order->orderAddresses->firstWhere('type', 'billing');
        $money = function ($amount) use ($order) {
            $formatted = number_format((float) $amount, $order->currency_decimal_places ?? 0);
            $symbol = $order->currency_symbol ?: $order->currency_code;
            return $order->currency_symbol_position === 'before' ? $symbol.$formatted : $formatted.' '.$symbol;
        };
    @endphp
    <div class="mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div><p class="text-sm text-slate-500">{{ __('admin.orders.placed_at') }} {{ ($order->placed_at ?? $order->created_at)?->format('Y-m-d H:i') }} · {{ $order->user_id ? __('admin.orders.registered_customer') : __('admin.orders.guest') }}</p><p class="mt-1 font-bold text-slate-950">{{ $order->customer_name }} · {{ $order->customer_email ?: $order->customer_phone }} · {{ $money($order->total_amount) }}</p></div>
        @include('admin.orders._status-summary')
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-extrabold">{{ __('admin.orders.items') }}</h2></div>
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200">
                    <thead><tr class="text-left text-xs font-bold uppercase text-slate-500"><th class="px-6 py-3">{{ __('admin.orders.product') }}</th><th class="px-4 py-3 text-right">{{ __('admin.orders.unit_price') }}</th><th class="px-4 py-3 text-right">{{ __('admin.orders.quantity') }}</th><th class="px-4 py-3 text-right">{{ __('admin.orders.subtotal') }}</th><th class="px-4 py-3 text-right">{{ __('admin.orders.tax') }}</th><th class="px-6 py-3 text-right">{{ __('admin.orders.line_total') }}</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">@foreach($order->orderItems as $item)<tr>
                        <td class="px-6 py-4"><div class="flex gap-3"><div class="grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-lg bg-slate-100 text-xs text-slate-400">@if($item->image)<img src="{{ $item->image }}" alt="" class="h-full w-full object-cover" onerror="this.remove();this.parentElement.textContent='—'">@else—@endif</div><div><p class="font-bold">{{ $item->product_name }}</p><p class="text-xs text-slate-500">{{ $item->variant_name ?: __('admin.orders.no_variant') }} · {{ $item->sku ?: $item->product_sku }}</p>@if($item->option_values_snapshot)<p class="mt-1 text-xs text-slate-500">{{ collect($item->option_values_snapshot)->map(fn($option) => ($option['option_label'] ?? $option['option_name'] ?? '').': '.($option['value_label'] ?? $option['value'] ?? ''))->implode(', ') }}</p>@endif<p class="mt-1 text-xs text-slate-400">{{ $item->tax_name ?: __('admin.orders.no_tax') }} · {{ rtrim(rtrim(number_format((float)$item->tax_rate, 4), '0'), '.') }}%</p></div></div></td>
                        <td class="px-4 py-4 text-right">{{ $money($item->price) }}</td><td class="px-4 py-4 text-right">{{ $item->quantity }}</td><td class="px-4 py-4 text-right">{{ $money($item->subtotal) }}</td><td class="px-4 py-4 text-right">{{ $money($item->tax_amount) }}</td><td class="px-6 py-4 text-right font-bold">{{ $money($item->total) }}</td>
                    </tr>@endforeach</tbody>
                </table></div>
                <dl class="ml-auto w-full max-w-md space-y-3 border-t border-slate-200 px-6 py-5 text-sm">
                    <div class="flex justify-between"><dt>{{ __('admin.orders.subtotal') }}</dt><dd>{{ $money($order->subtotal) }}</dd></div>
                    <div class="flex justify-between"><dt>{{ __('admin.orders.discount') }} @if($order->coupon_snapshot)({{ $order->coupon_snapshot['code'] ?? '' }})@endif</dt><dd>-{{ $money($order->discount_amount) }}</dd></div>
                    <div class="flex justify-between"><dt>{{ __('admin.orders.taxable_amount') }}</dt><dd>{{ $money($order->orderItems->sum(fn($item) => (float)$item->taxable_amount)) }}</dd></div>
                    <div class="flex justify-between"><dt>{{ __('admin.orders.tax') }}</dt><dd>{{ $money($order->tax_amount) }}</dd></div>
                    <div class="flex justify-between"><dt>{{ __('admin.orders.shipping') }}</dt><dd>{{ $money($order->shipping_fee) }}</dd></div>
                    <div class="flex justify-between border-t border-slate-200 pt-3 text-base font-black"><dt>{{ __('admin.orders.total') }}</dt><dd>{{ $money($order->total_amount) }}</dd></div>
                </dl>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="font-extrabold">{{ __('admin.orders.coupon_info') }}</h2>
                    @if($order->coupon_snapshot)
                        <dl class="mt-3 space-y-2 text-sm"><div><dt class="text-slate-400">{{ __('admin.orders.coupon_code') }}</dt><dd class="font-semibold">{{ $order->coupon_snapshot['code'] ?? '—' }}</dd></div><div><dt class="text-slate-400">{{ __('admin.orders.discount') }}</dt><dd>{{ $money($order->coupon_snapshot['discount_amount'] ?? $order->discount_amount) }}</dd></div></dl>
                    @else
                        <p class="mt-3 text-sm text-slate-500">{{ __('admin.orders.no_coupon') }}</p>
                    @endif
                </section>
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="font-extrabold">{{ __('admin.orders.tax_summary') }}</h2>
                    <div class="mt-3 space-y-3 text-sm">@forelse($order->tax_snapshot ?? [] as $tax)<div class="flex justify-between gap-4"><span>{{ $tax['tax_name'] ?? __('admin.orders.tax') }} ({{ rtrim(rtrim(number_format((float)($tax['tax_rate'] ?? 0), 4), '0'), '.') }}%)</span><strong>{{ $money($tax['tax_amount'] ?? 0) }}</strong></div>@empty<p class="text-slate-500">{{ __('admin.orders.no_tax_lines') }}</p>@endforelse</div>
                </section>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                @foreach(['shipping' => $shipping, 'billing' => $billing] as $type => $address)
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="font-extrabold">{{ __('admin.orders.'.$type.'_address') }}</h2>@if($address)<address class="mt-3 not-italic text-sm leading-6 text-slate-600"><strong class="text-slate-900">{{ $address->full_name }}</strong><br>{{ $address->phone }}<br>{{ $address->address_line }}<br>{{ collect([$address->ward,$address->district,$address->province,$address->country_code])->filter()->implode(', ') }}</address>@else<p class="mt-3 text-sm text-slate-500">{{ __('admin.orders.address_missing') }}</p>@endif</section>
                @endforeach
            </div>

            <section class="grid gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:grid-cols-2">
                <div><h2 class="font-extrabold">{{ __('admin.orders.customer_snapshot') }}</h2><dl class="mt-3 space-y-2 text-sm"><div><dt class="text-slate-400">{{ __('admin.orders.name') }}</dt><dd class="font-semibold">{{ $order->customer_name }}</dd></div><div><dt class="text-slate-400">Email</dt><dd>{{ $order->customer_email ?: '—' }}</dd></div><div><dt class="text-slate-400">{{ __('admin.orders.phone') }}</dt><dd>{{ $order->customer_phone }}</dd></div><div><dt class="text-slate-400">{{ __('admin.orders.customer_note') }}</dt><dd class="whitespace-pre-line">{{ $order->note ?: '—' }}</dd></div></dl></div>
                <div><h2 class="font-extrabold">{{ __('admin.orders.payment_currency') }}</h2><dl class="mt-3 space-y-2 text-sm"><div><dt class="text-slate-400">{{ __('admin.orders.payment_method') }}</dt><dd class="font-semibold">{{ $order->payment_method_name ?: strtoupper($order->payment_method) }}</dd></div><div><dt class="text-slate-400">{{ __('admin.orders.currency') }}</dt><dd>{{ $order->currency_code }} · {{ __('admin.orders.rate') }} {{ $order->exchange_rate }}</dd></div><div><dt class="text-slate-400">{{ __('admin.orders.payment_amount') }}</dt><dd>{{ $money($order->orderPayments->first()?->amount ?? $order->total_amount) }}</dd></div><div><dt class="text-slate-400">{{ __('admin.orders.paid_at') }}</dt><dd>{{ ($order->orderPayments->first()?->paid_at ?? $order->payment?->paid_at)?->format('Y-m-d H:i') ?: '—' }}</dd></div><div><dt class="text-slate-400">{{ __('admin.orders.instruction') }}</dt><dd class="whitespace-pre-line">{{ $order->payment_instruction ?: '—' }}</dd></div></dl></div>
            </section>

            @if($order->paymentTransactions->isNotEmpty())
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-extrabold">{{ __('admin.orders.payment_transactions') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('admin.orders.payment_transactions_help') }}</p></div>
                    <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">{{ __('admin.orders.transaction_number') }}</th><th class="px-5 py-3">{{ __('admin.orders.gateway') }}</th><th class="px-5 py-3">{{ __('admin.orders.gateway_transaction') }}</th><th class="px-5 py-3 text-right">{{ __('admin.orders.payment_amount') }}</th><th class="px-5 py-3">{{ __('admin.orders.payment_status') }}</th><th class="px-5 py-3">{{ __('admin.orders.created_at') }}</th><th class="px-5 py-3">{{ __('admin.orders.paid_at') }}</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">@foreach($order->paymentTransactions->sortByDesc('id') as $transaction)<tr><td class="px-5 py-4 font-bold text-indigo-700">{{ $transaction->transaction_number }}</td><td class="px-5 py-4">{{ strtoupper($transaction->gateway_code) }}</td><td class="px-5 py-4">{{ $transaction->gateway_transaction_id ?: '—' }}</td><td class="px-5 py-4 text-right font-bold">{{ number_format((float)$transaction->amount, $order->currency_decimal_places ?? 0) }} {{ $transaction->currency_code }}</td><td class="px-5 py-4"><x-admin.order-status :status="$transaction->status" type="payment" /></td><td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $transaction->created_at?->format('Y-m-d H:i') }}</td><td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $transaction->paid_at?->format('Y-m-d H:i') ?: '—' }}</td></tr>@endforeach</tbody>
                    </table></div>
                </section>
            @endif

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-extrabold">{{ __('admin.orders.timeline') }}</h2>
                <div class="mt-5">@include('admin.orders._timeline')</div>
                <form data-order-action action="{{ route('admin.orders.notes.store', $order) }}" method="POST" class="mt-6 border-t border-slate-200 pt-5">@csrf<label class="text-sm font-bold">{{ __('admin.orders.add_note') }}</label><textarea name="note" required rows="3" class="mt-2 w-full rounded-xl border-slate-300 text-sm" placeholder="{{ __('admin.orders.note_placeholder') }}"></textarea><div data-order-errors class="mt-2 hidden rounded-xl bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700"></div><button class="mt-3 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-60">{{ __('admin.orders.save_note') }}</button></form>
            </section>
        </div>

        <aside>@include('admin.orders._management')</aside>
    </div>

    <div id="order-cancel-modal" class="pointer-events-none fixed inset-0 z-[75] grid place-items-center p-4 opacity-0 transition" aria-hidden="true">
        <div data-cancel-backdrop class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"></div>
        <section class="relative z-10 w-full max-w-lg rounded-2xl bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="cancel-order-title">
            <div class="border-b border-slate-200 px-6 py-5"><h2 id="cancel-order-title" class="text-lg font-extrabold text-rose-800">{{ __('admin.orders.cancel_order') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('admin.orders.cancel_confirm') }}</p></div>
            <form data-order-action data-cancel-form action="{{ route('admin.orders.cancel', $order) }}" method="POST" class="p-6">@csrf
                <label class="text-sm font-bold">{{ __('admin.orders.cancel_reason') }}</label><textarea name="reason" required rows="3" class="mt-2 w-full rounded-xl border-slate-300 text-sm"></textarea>
                <label class="mt-4 flex items-start gap-3 rounded-xl border border-sky-200 bg-sky-50 p-4"><input type="checkbox" name="restock" value="1" checked class="mt-0.5 rounded border-sky-300 text-sky-600"><span><strong class="block text-sm text-sky-900">{{ __('admin.orders.restock_items') }}</strong><span class="text-xs text-sky-700">{{ __('admin.orders.restock_help') }}</span></span></label>
                <div data-order-errors class="mt-3 hidden rounded-xl bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700"></div>
                <div class="mt-6 flex justify-end gap-3"><button type="button" data-close-order-cancel class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold">{{ __('admin.common.cancel') }}</button><button class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-60">{{ __('admin.orders.confirm_cancel') }}</button></div>
            </form>
        </section>
    </div>

    <div id="mark-paid-modal" class="pointer-events-none fixed inset-0 z-[75] grid place-items-center p-4 opacity-0 transition" aria-hidden="true">
        <div data-mark-paid-backdrop class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"></div>
        <section class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="mark-paid-title">
            <div class="border-b border-slate-200 px-6 py-5"><h2 id="mark-paid-title" class="text-lg font-extrabold text-slate-950">{{ __('admin.orders.mark_paid') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('admin.orders.mark_paid_confirm') }}</p></div>
            <form data-order-action data-mark-paid-form action="{{ route('admin.orders.mark-paid', $order) }}" method="POST" class="p-6">@csrf
                <div data-order-errors class="hidden rounded-xl bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700"></div>
                <div class="flex justify-end gap-3"><button type="button" data-close-mark-paid class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold">{{ __('admin.common.cancel') }}</button><button class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-60">{{ __('admin.orders.confirm_mark_paid') }}</button></div>
            </form>
        </section>
    </div>
@endsection
@push('scripts')@vite('resources/js/admin-orders.js')@endpush
