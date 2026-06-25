@extends('layouts.admin')
@section('title', __('admin.reports.sales_title'))
@section('content')
    @include('admin.reports._filters', ['resetRoute' => route('admin.reports.sales'), 'exportRoute' => route('admin.reports.export', ['report' => 'sales'] + $filters)])
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-admin.report-card :label="__('admin.reports.gross_revenue')" :value="$service->formatBase($summary->gross_revenue)" tone="emerald" />
        <x-admin.report-card :label="__('admin.reports.paid_revenue')" :value="$service->formatBase($summary->paid_revenue)" tone="indigo" />
        <x-admin.report-card :label="__('admin.reports.unpaid_revenue')" :value="$service->formatBase($summary->unpaid_revenue)" tone="rose" />
        <x-admin.report-card :label="__('admin.reports.net_revenue')" :value="$service->formatBase($summary->net_revenue)" tone="emerald" />
        <x-admin.report-card :label="__('admin.reports.total_orders')" :value="$summary->total_orders" tone="violet" />
        <x-admin.report-card :label="__('admin.reports.average_order_value')" :value="$service->formatBase($summary->average_order_value)" tone="sky" />
        <x-admin.report-card :label="__('admin.reports.discount')" :value="$service->formatBase($summary->discount_amount)" tone="amber" />
        <x-admin.report-card :label="__('admin.reports.tax')" :value="$service->formatBase($summary->tax_amount)" tone="sky" />
        <x-admin.report-card :label="__('admin.reports.shipping')" :value="$service->formatBase($summary->shipping_amount)" tone="indigo" />
    </section>
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-extrabold">{{ __('admin.reports.sales_trend') }}</h2></div>
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">{{ __('admin.reports.date') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.orders') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.gross_revenue') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.paid_revenue') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.discount') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.tax') }}</th></tr></thead>
            <tbody class="divide-y divide-slate-100">@forelse($trend as $row)<tr><td class="px-5 py-4 font-bold">{{ $row->report_date }}</td><td class="px-5 py-4 text-right">{{ $row->orders_count }}</td><td class="px-5 py-4 text-right font-bold">{{ $service->formatBase($row->gross_revenue) }}</td><td class="px-5 py-4 text-right">{{ $service->formatBase($row->paid_revenue) }}</td><td class="px-5 py-4 text-right">{{ $service->formatBase($row->discount_amount) }}</td><td class="px-5 py-4 text-right">{{ $service->formatBase($row->tax_amount) }}</td></tr>@empty<tr><td colspan="6" class="px-6 py-14 text-center text-slate-500">{{ __('admin.reports.empty') }}</td></tr>@endforelse</tbody>
        </table></div>
        @if($trend->hasPages())<div class="border-t px-5 py-4">{{ $trend->links() }}</div>@endif
    </div>
@endsection
