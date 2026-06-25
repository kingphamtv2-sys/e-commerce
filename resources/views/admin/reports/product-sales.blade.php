@extends('layouts.admin')
@section('title', __('admin.reports.product-sales_title'))
@section('content')
    @include('admin.reports._filters', ['showProducts' => true, 'resetRoute' => route('admin.reports.product-sales'), 'exportRoute' => route('admin.reports.export', ['report' => 'product-sales'] + $filters)])
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.report-card :label="__('admin.reports.quantity_sold')" :value="$summary->quantity_sold" tone="indigo" />
        <x-admin.report-card :label="__('admin.reports.unique_products')" :value="$summary->unique_products" tone="violet" />
        <x-admin.report-card :label="__('admin.reports.product_revenue')" :value="$service->formatBase($summary->product_revenue)" tone="emerald" />
        <x-admin.report-card :label="__('admin.reports.best_seller')" :value="$bestSeller?->product_name ?? '—'" :hint="$bestSeller ? __('admin.reports.units', ['count' => $bestSeller->quantity_sold]) : null" tone="amber" />
    </section>
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">{{ __('admin.reports.product_snapshot') }}</th><th class="px-5 py-3">{{ __('admin.reports.variant') }}</th><th class="px-5 py-3">SKU</th><th class="px-5 py-3 text-right">{{ __('admin.reports.quantity') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.orders') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.subtotal') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.tax') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.revenue') }}</th></tr></thead>
            <tbody class="divide-y divide-slate-100">@forelse($items as $item)<tr><td class="px-5 py-4 font-bold">{{ $item->product_name }}</td><td class="px-5 py-4 text-slate-600">{{ $item->variant_name ?: '—' }}</td><td class="px-5 py-4 text-slate-600">{{ $item->snapshot_sku ?: '—' }}</td><td class="px-5 py-4 text-right font-bold">{{ $item->quantity_sold }}</td><td class="px-5 py-4 text-right">{{ $item->orders_count }}</td><td class="px-5 py-4 text-right">{{ $service->formatBase($item->subtotal_amount) }}</td><td class="px-5 py-4 text-right">{{ $service->formatBase($item->tax_amount) }}</td><td class="px-5 py-4 text-right font-bold text-emerald-700">{{ $service->formatBase($item->total_revenue) }}</td></tr>@empty<tr><td colspan="8" class="px-6 py-14 text-center text-slate-500">{{ __('admin.reports.empty') }}</td></tr>@endforelse</tbody>
        </table></div>
        @if($items->hasPages())<div class="border-t px-5 py-4">{{ $items->links() }}</div>@endif
    </div>
@endsection
