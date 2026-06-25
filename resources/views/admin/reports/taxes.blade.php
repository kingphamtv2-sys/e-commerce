@extends('layouts.admin')
@section('title', __('admin.reports.taxes_title'))
@section('content')
    @include('admin.reports._filters', ['showProducts' => true, 'resetRoute' => route('admin.reports.taxes'), 'exportRoute' => route('admin.reports.export', ['report' => 'taxes'] + $filters)])
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.report-card :label="__('admin.reports.total_tax')" :value="$service->formatBase($summary->total_tax)" tone="sky" />
        <x-admin.report-card :label="__('admin.reports.taxable_amount')" :value="$service->formatBase($summary->taxable_amount)" tone="indigo" />
        <x-admin.report-card :label="__('admin.reports.tax_groups')" :value="$summary->tax_classes_count" tone="violet" />
        <x-admin.report-card :label="__('admin.reports.orders_with_tax')" :value="$summary->orders_count" tone="emerald" />
    </section>
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">{{ __('admin.reports.tax_name_snapshot') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.tax_rate_snapshot') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.taxable_amount') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.tax') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.orders') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.items') }}</th></tr></thead>
            <tbody class="divide-y divide-slate-100">@forelse($taxes as $tax)<tr><td class="px-5 py-4 font-bold">{{ $tax->tax_name }}</td><td class="px-5 py-4 text-right">{{ number_format((float)$tax->tax_rate, 2) }}%</td><td class="px-5 py-4 text-right">{{ $service->formatBase($tax->taxable_amount) }}</td><td class="px-5 py-4 text-right font-bold text-sky-700">{{ $service->formatBase($tax->tax_amount) }}</td><td class="px-5 py-4 text-right">{{ $tax->orders_count }}</td><td class="px-5 py-4 text-right">{{ $tax->items_count }}</td></tr>@empty<tr><td colspan="6" class="px-6 py-14 text-center text-slate-500">{{ __('admin.reports.empty') }}</td></tr>@endforelse</tbody>
        </table></div>
        @if($taxes->hasPages())<div class="border-t px-5 py-4">{{ $taxes->links() }}</div>@endif
    </div>
@endsection
