@extends('layouts.account')

@section('title', __('account.orders').' - '.$siteName)
@section('account-title', __('account.orders'))

@section('account-content')
<section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
    <form method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
        <input name="q" value="{{ $orderFilters['q'] ?? '' }}" placeholder="{{ __('account.search_order') }}" class="rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 xl:col-span-2">
        <select name="status" class="rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('account.all_order_statuses') }}</option>
            @foreach(['pending','confirmed','processing','shipped','completed','cancelled','refunded'] as $status)
                <option value="{{ $status }}" @selected(($orderFilters['status'] ?? '') === $status)>{{ __('account.statuses.'.$status) }}</option>
            @endforeach
        </select>
        <select name="payment_status" class="rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('account.all_payment_statuses') }}</option>
            @foreach(['unpaid','pending','paid','failed','cancelled','refunded'] as $status)
                <option value="{{ $status }}" @selected(($orderFilters['payment_status'] ?? '') === $status)>{{ __('account.statuses.'.$status) }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-extrabold text-white">{{ __('account.filter') }}</button>
    </form>
</section>

<div class="mt-5 space-y-4">
    @forelse($orders as $order)
        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <a href="{{ route('account.orders.show', $order) }}" class="text-lg font-extrabold text-slate-950 hover:text-indigo-600">{{ $order->order_code }}</a>
                    <p class="mt-1 text-xs font-semibold text-slate-400">{{ ($order->placed_at ?? $order->created_at)->format('Y-m-d H:i') }}</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3 lg:flex lg:items-center">
                    <div><p class="text-[10px] font-extrabold uppercase text-slate-400">{{ __('account.order_status') }}</p><div class="mt-1">@include('account.partials.status', ['status' => $order->order_status])</div></div>
                    <div><p class="text-[10px] font-extrabold uppercase text-slate-400">{{ __('account.payment_status') }}</p><div class="mt-1">@include('account.partials.status', ['status' => $order->payment_status])</div></div>
                    <div class="sm:text-right"><p class="text-[10px] font-extrabold uppercase text-slate-400">{{ __('account.total') }}</p><p class="mt-1 font-extrabold text-slate-950">{{ $money($order, $order->total_amount) }}</p></div>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                <p class="text-sm font-bold text-slate-500">{{ $order->payment_method_name ?: strtoupper($order->payment_method) }}</p>
                <a href="{{ route('account.orders.show', $order) }}" class="text-sm font-extrabold text-indigo-600">{{ __('account.view_order') }} →</a>
            </div>
        </article>
    @empty
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
            <p class="font-extrabold text-slate-950">{{ __('account.no_orders') }}</p>
            <a href="{{ route('products.index') }}" class="mt-5 inline-flex rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-bold text-white">{{ __('account.continue_shopping') }}</a>
        </div>
    @endforelse
</div>

@if($orders->hasPages())
    <div class="mt-6">{{ $orders->links() }}</div>
@endif
@endsection
