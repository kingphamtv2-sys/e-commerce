@extends('layouts.admin')
@section('title', __('admin.reports.coupons_title'))
@section('content')
    @include('admin.reports._filters', ['showPayment' => false, 'resetRoute' => route('admin.reports.coupons'), 'exportRoute' => route('admin.reports.export', ['report' => 'coupons'] + $filters)])
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <x-admin.report-card :label="__('admin.reports.coupon_usages')" :value="$summary->total_usages" />
        <x-admin.report-card :label="__('admin.reports.orders_with_coupon')" :value="$summary->orders_count" tone="violet" />
        <x-admin.report-card :label="__('admin.reports.total_discount')" :value="$service->formatBase($summary->total_discount)" tone="rose" />
        <x-admin.report-card :label="__('admin.reports.average_discount')" :value="$service->formatBase($summary->average_discount)" tone="amber" />
        <x-admin.report-card :label="__('admin.reports.top_coupon')" :value="$topCoupon?->coupon_code ?? '—'" :hint="$topCoupon ? __('admin.reports.uses', ['count' => $topCoupon->usage_count]) : null" tone="emerald" />
    </section>
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">{{ __('admin.reports.coupon_code') }}</th><th class="px-5 py-3">{{ __('admin.reports.coupon_name') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.uses_label') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.orders') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.discount') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.revenue') }}</th><th class="px-5 py-3">{{ __('admin.reports.last_used') }}</th></tr></thead>
            <tbody class="divide-y divide-slate-100">@forelse($coupons as $coupon)<tr><td class="px-5 py-4 font-black text-indigo-700">{{ $coupon->coupon_code }}</td><td class="px-5 py-4">{{ $coupon->coupon_name ?: '—' }}</td><td class="px-5 py-4 text-right">{{ $coupon->usage_count }}</td><td class="px-5 py-4 text-right">{{ $coupon->orders_count }}</td><td class="px-5 py-4 text-right font-bold text-rose-700">{{ $service->formatBase($coupon->total_discount) }}</td><td class="px-5 py-4 text-right">{{ $service->formatBase($coupon->total_revenue) }}</td><td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $coupon->last_used }}</td></tr>@empty<tr><td colspan="7" class="px-6 py-14 text-center text-slate-500">{{ __('admin.reports.empty') }}</td></tr>@endforelse</tbody>
        </table></div>
        @if($coupons->hasPages())<div class="border-t px-5 py-4">{{ $coupons->links() }}</div>@endif
    </div>
@endsection
