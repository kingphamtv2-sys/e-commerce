@extends('layouts.admin')
@section('title', __('admin.reports.orders_title'))
@section('content')
    @include('admin.reports._filters', ['resetRoute' => route('admin.reports.orders'), 'exportRoute' => route('admin.reports.export', ['report' => 'orders'] + $filters)])
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-admin.report-card :label="__('admin.reports.total_orders')" :value="$summary['total']" />
        <x-admin.report-card :label="__('admin.reports.pending')" :value="$summary['pending']" tone="amber" />
        <x-admin.report-card :label="__('admin.reports.confirmed')" :value="$summary['confirmed']" tone="indigo" />
        <x-admin.report-card :label="__('admin.reports.processing')" :value="$summary['processing']" tone="violet" />
        <x-admin.report-card :label="__('admin.reports.completed')" :value="$summary['completed']" tone="emerald" />
        <x-admin.report-card :label="__('admin.reports.cancelled')" :value="$summary['cancelled'].' ('.$summary['cancellation_rate'].'%)'" tone="rose" />
    </section>
    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(18rem,.55fr)_minmax(0,1.45fr)]">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-extrabold">{{ __('admin.reports.status_breakdown') }}</h2>
            <div class="mt-4 divide-y divide-slate-100">@foreach(\App\Services\ReportFilterService::ORDER_STATUSES as $status)<div class="flex items-center justify-between gap-3 py-3"><x-admin.order-status :status="$status" /><div class="text-right"><p class="font-black">{{ (int)($breakdown->get($status)?->orders_count ?? 0) }}</p><p class="text-xs text-slate-500">{{ $service->formatBase($breakdown->get($status)?->total_amount ?? 0) }}</p></div></div>@endforeach</div>
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">{{ __('admin.reports.order') }}</th><th class="px-5 py-3">{{ __('admin.reports.customer') }}</th><th class="px-5 py-3">{{ __('admin.reports.status') }}</th><th class="px-5 py-3">{{ __('admin.reports.payment') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.total') }}</th><th class="px-5 py-3">{{ __('admin.reports.date') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">@forelse($orders as $order)<tr><td class="px-5 py-4"><a class="font-bold text-indigo-700" href="{{ route('admin.orders.show', $order) }}">{{ $order->order_code }}</a></td><td class="px-5 py-4"><p class="font-semibold">{{ $order->customer_name }}</p><p class="text-xs text-slate-500">{{ $order->customer_email ?: $order->customer_phone }}</p></td><td class="px-5 py-4"><x-admin.order-status :status="$order->order_status" /></td><td class="px-5 py-4"><x-admin.order-status :status="$order->payment_status" type="payment" /><p class="mt-1 text-xs text-slate-500">{{ strtoupper($order->payment_method) }}</p></td><td class="px-5 py-4 text-right font-bold">{{ $service->formatOrderAmount($order->total_amount, $order) }}</td><td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ ($order->placed_at ?? $order->created_at)?->format('Y-m-d H:i') }}</td></tr>@empty<tr><td colspan="6" class="px-6 py-14 text-center text-slate-500">{{ __('admin.reports.empty') }}</td></tr>@endforelse</tbody>
            </table></div>
            @if($orders->hasPages())<div class="border-t px-5 py-4">{{ $orders->links() }}</div>@endif
        </div>
    </div>
@endsection
