@extends('layouts.admin')
@section('title', __('admin.reports.inventory_title'))
@section('content')
    @include('admin.reports._filters', ['showStatuses' => false, 'showPayment' => false, 'showProducts' => true, 'showStock' => true, 'resetRoute' => route('admin.reports.inventory'), 'exportRoute' => route('admin.reports.export', ['report' => 'inventory'] + $filters)])
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <x-admin.report-card :label="__('admin.reports.stock_items')" :value="$summary->total_items" />
        <x-admin.report-card :label="__('admin.reports.low_stock')" :value="$summary->low_stock" tone="amber" />
        <x-admin.report-card :label="__('admin.reports.out_of_stock')" :value="$summary->out_of_stock" tone="rose" />
        <x-admin.report-card :label="__('admin.reports.available')" :value="$summary->available_quantity" tone="emerald" />
        <x-admin.report-card :label="__('admin.reports.reserved')" :value="$summary->reserved_quantity" tone="violet" />
    </section>
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">{{ __('admin.reports.product') }}</th><th class="px-5 py-3">{{ __('admin.reports.variant') }}</th><th class="px-5 py-3">SKU</th><th class="px-5 py-3 text-right">{{ __('admin.reports.quantity') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.reserved') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.available') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.threshold') }}</th><th class="px-5 py-3">{{ __('admin.reports.stock_status') }}</th><th class="px-5 py-3">{{ __('admin.reports.updated_at') }}</th></tr></thead>
            <tbody class="divide-y divide-slate-100">@forelse($stocks as $stock)<tr><td class="px-5 py-4"><a href="{{ route('admin.inventory.show', $stock) }}" class="font-bold text-indigo-700">{{ app(\App\Services\ProductService::class)->name($stock->product) }}</a></td><td class="px-5 py-4">{{ app(\App\Services\InventoryService::class)->variantName($stock) }}</td><td class="px-5 py-4">{{ app(\App\Services\InventoryService::class)->sku($stock) }}</td><td class="px-5 py-4 text-right">{{ $stock->quantity }}</td><td class="px-5 py-4 text-right">{{ $stock->reserved_quantity }}</td><td class="px-5 py-4 text-right font-bold">{{ $stock->availableQuantity() }}</td><td class="px-5 py-4 text-right">{{ $stock->low_stock_threshold }}</td><td class="px-5 py-4"><span @class(['rounded-full px-2.5 py-1 text-xs font-bold','bg-emerald-50 text-emerald-700' => $stock->stockStatus() === 'in_stock','bg-amber-50 text-amber-700' => $stock->stockStatus() === 'low_stock','bg-rose-50 text-rose-700' => $stock->stockStatus() === 'out_of_stock'])>{{ __("admin.reports.{$stock->stockStatus()}") }}</span></td><td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $stock->updated_at?->format('Y-m-d H:i') }}</td></tr>@empty<tr><td colspan="9" class="px-6 py-14 text-center text-slate-500">{{ __('admin.reports.empty') }}</td></tr>@endforelse</tbody>
        </table></div>
        @if($stocks->hasPages())<div class="border-t px-5 py-4">{{ $stocks->links() }}</div>@endif
    </div>
@endsection
