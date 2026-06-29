@extends('layouts.admin')
@section('title', __('admin.orders.title'))
@section('content')
    <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-2 xl:grid-cols-4">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('admin.orders.search') }}" class="rounded-xl border-slate-300 text-sm xl:col-span-2">
        <select name="order_status" class="rounded-xl border-slate-300 text-sm">
            <option value="">{{ __('admin.orders.all_order_statuses') }}</option>
            @foreach(['pending','confirmed','processing','shipped','completed','cancelled','refunded'] as $status)
                <option value="{{ $status }}" @selected(($filters['order_status'] ?? '') === $status)>{{ __('admin.orders.status_'.$status) }}</option>
            @endforeach
        </select>
        <select name="payment_status" class="rounded-xl border-slate-300 text-sm">
            <option value="">{{ __('admin.orders.all_payment_statuses') }}</option>
            @foreach(['unpaid','pending','paid','failed','refunded','cancelled'] as $status)
                <option value="{{ $status }}" @selected(($filters['payment_status'] ?? '') === $status)>{{ __('admin.orders.payment_'.$status) }}</option>
            @endforeach
        </select>
        <select name="fulfillment_status" class="rounded-xl border-slate-300 text-sm">
            <option value="">{{ __('admin.orders.all_fulfillment_statuses') }}</option>
            @foreach(['unfulfilled','processing','shipped','delivered','cancelled'] as $status)
                <option value="{{ $status }}" @selected(($filters['fulfillment_status'] ?? '') === $status)>{{ __('admin.orders.fulfillment_'.$status) }}</option>
            @endforeach
        </select>
        <select name="payment_method" class="rounded-xl border-slate-300 text-sm">
            <option value="">{{ __('admin.orders.all_payment_methods') }}</option>
            <option value="cod" @selected(($filters['payment_method'] ?? '') === 'cod')>COD</option>
            <option value="online" @selected(($filters['payment_method'] ?? '') === 'online')>{{ __('admin.menu.online_payment') }}</option>
        </select>
        <select name="customer_type" class="rounded-xl border-slate-300 text-sm">
            <option value="">{{ __('admin.orders.all_customer_types') }}</option>
            <option value="guest" @selected(($filters['customer_type'] ?? '') === 'guest')>{{ __('admin.orders.guest') }}</option>
            <option value="customer" @selected(($filters['customer_type'] ?? '') === 'customer')>{{ __('admin.orders.registered_customer') }}</option>
        </select>
        <select name="coupon_used" class="rounded-xl border-slate-300 text-sm">
            <option value="">{{ __('admin.orders.coupon_any') }}</option>
            <option value="yes" @selected(($filters['coupon_used'] ?? '') === 'yes')>{{ __('admin.orders.coupon_yes') }}</option>
            <option value="no" @selected(($filters['coupon_used'] ?? '') === 'no')>{{ __('admin.orders.coupon_no') }}</option>
        </select>
        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="rounded-xl border-slate-300 text-sm" aria-label="{{ __('admin.orders.date_from') }}">
        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="rounded-xl border-slate-300 text-sm" aria-label="{{ __('admin.orders.date_to') }}">
        <select name="sort" class="rounded-xl border-slate-300 text-sm">
            @foreach(['newest','oldest','total_high','total_low'] as $sort)<option value="{{ $sort }}" @selected(($filters['sort'] ?? 'newest') === $sort)>{{ __('admin.orders.sort_'.$sort) }}</option>@endforeach
        </select>
        <div class="flex gap-2 xl:justify-end">
            <a href="{{ route('admin.orders.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-600">{{ __('admin.inventory.reset') }}</a>
            <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white">{{ __('admin.inventory.filter') }}</button>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead><tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    <th class="px-5 py-4">{{ __('admin.orders.order') }}</th><th class="px-5 py-4">{{ __('admin.orders.customer') }}</th>
                    <th class="px-5 py-4">{{ __('admin.orders.payment_method') }}</th><th class="px-5 py-4">{{ __('admin.orders.order_status') }}</th><th class="px-5 py-4">{{ __('admin.orders.payment_status') }}</th>
                    <th class="px-5 py-4">{{ __('admin.orders.fulfillment') }}</th>
                    <th class="px-5 py-4 text-right">{{ __('admin.orders.total') }}</th><th class="px-5 py-4">{{ __('admin.orders.placed_at') }}</th>
                    <th class="px-5 py-4 text-right">{{ __('admin.common.actions') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4"><a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-indigo-700">{{ $order->order_code }}</a></td>
                            <td class="px-5 py-4"><p class="font-semibold text-slate-900">{{ $order->customer_name }}</p><p class="text-xs text-slate-500">{{ $order->customer_email ?: $order->customer_phone }}</p></td>
                            <td class="px-5 py-4 text-sm font-semibold text-slate-600">{{ $order->payment_method_name ?: strtoupper($order->payment_method) }}</td>
                            <td class="px-5 py-4"><x-admin.order-status :status="$order->order_status" /></td>
                            <td class="px-5 py-4"><x-admin.order-status :status="$order->payment_status" type="payment" /></td>
                            <td class="px-5 py-4"><x-admin.order-status :status="$order->fulfillment_status" type="fulfillment" /></td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-bold">{{ number_format((float)$order->total_amount, $order->currency_decimal_places ?? 0) }} {{ $order->currency_code }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-500">{{ ($order->placed_at ?? $order->created_at)?->format('Y-m-d H:i') }}</td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('admin.orders.show', $order) }}" class="rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-bold text-indigo-700">{{ __('admin.orders.view') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-6 py-16 text-center text-sm text-slate-500">{{ __('admin.orders.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())<div class="border-t border-slate-200 px-6 py-4">{{ $orders->links() }}</div>@endif
    </div>
@endsection
