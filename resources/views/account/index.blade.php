@extends('layouts.account')

@section('title', __('account.dashboard').' - '.$siteName)
@section('account-title', __('account.dashboard'))

@section('account-content')
    <section class="rounded-3xl bg-gradient-to-br from-indigo-600 to-violet-700 p-6 text-white shadow-xl shadow-indigo-100 sm:p-8">
        <p class="text-sm font-bold text-indigo-100">{{ __('account.welcome', ['name' => auth()->user()->name]) }}</p>
        <h2 class="mt-2 max-w-2xl text-2xl font-extrabold sm:text-3xl">{{ __('account.dashboard_intro') }}</h2>
    </section>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">{{ __('account.pending_orders') }}</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $pendingOrders }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">{{ __('account.unpaid_orders') }}</p>
            <p class="mt-2 text-3xl font-extrabold text-slate-950">{{ $unpaidOrders }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2 xl:col-span-1">
            <p class="text-xs font-extrabold uppercase tracking-wide text-slate-400">{{ __('account.default_shipping') }}</p>
            @if($defaultAddress)
                <p class="mt-2 text-sm font-extrabold text-slate-950">{{ $defaultAddress->recipient_name }}</p>
                <p class="mt-1 text-sm leading-6 text-slate-500">{{ $defaultAddress->formatted() }}</p>
            @else
                <a href="{{ route('account.addresses.create') }}" class="mt-3 inline-flex text-sm font-extrabold text-indigo-600">{{ __('account.add_address') }} →</a>
            @endif
        </div>
    </div>

    <section class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
            <h2 class="font-extrabold text-slate-950">{{ __('account.recent_orders') }}</h2>
            <a href="{{ route('account.orders.index') }}" class="text-sm font-extrabold text-indigo-600">{{ __('account.view_all') }}</a>
        </div>
        @forelse($recentOrders as $order)
            <a href="{{ route('account.orders.show', $order) }}" class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 transition last:border-0 hover:bg-slate-50 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <p class="font-extrabold text-slate-950">{{ $order->order_code }}</p>
                    <p class="mt-1 text-xs font-semibold text-slate-400">{{ ($order->placed_at ?? $order->created_at)->format('Y-m-d H:i') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @include('account.partials.status', ['status' => $order->order_status])
                    <span class="text-sm font-extrabold text-slate-950">{{ app(\App\Services\OrderEmailDataService::class)->formatMoney($order, $order->total_amount) }}</span>
                </div>
            </a>
        @empty
            <div class="px-6 py-12 text-center">
                <p class="font-extrabold text-slate-950">{{ __('account.no_orders') }}</p>
                <a href="{{ route('products.index') }}" class="mt-4 inline-flex rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-bold text-white">{{ __('account.continue_shopping') }}</a>
            </div>
        @endforelse
    </section>
@endsection
