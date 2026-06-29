@extends('layouts.admin')
@section('title', __('admin.reports.payments_title'))
@section('content')
    @include('admin.reports._filters', ['resetRoute' => route('admin.reports.payments'), 'exportRoute' => route('admin.reports.export', ['report' => 'payments'] + $filters)])
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-admin.report-card :label="__('admin.reports.total_payments')" :value="$summary->total_payments" />
        <x-admin.report-card :label="__('admin.reports.paid_amount')" :value="$service->formatBase($summary->paid_amount)" tone="emerald" />
        <x-admin.report-card :label="__('admin.reports.unpaid_amount')" :value="$service->formatBase($summary->unpaid_amount)" tone="rose" />
        <x-admin.report-card :label="__('admin.reports.cod_orders')" :value="$summary->cod_orders" tone="violet" />
        <x-admin.report-card :label="__('admin.reports.cod_unpaid_amount')" :value="$service->formatBase($summary->cod_unpaid_amount)" :hint="__('admin.reports.cod_unpaid_hint')" tone="rose" />
        <x-admin.report-card :label="__('admin.reports.failed_payments')" :value="$summary->failed_payments" tone="amber" />
    </section>
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">{{ __('admin.reports.payment_method') }}</th><th class="px-5 py-3">{{ __('admin.reports.payment_status') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.orders') }}</th><th class="px-5 py-3 text-right">{{ __('admin.reports.amount') }}</th><th class="px-5 py-3">{{ __('admin.reports.last_payment') }}</th></tr></thead>
            <tbody class="divide-y divide-slate-100">@forelse($payments as $payment)<tr @class(['bg-rose-50/60' => $payment->payment_method_code === 'cod' && in_array($payment->payment_status, ['unpaid','pending'], true)])><td class="px-5 py-4 font-bold">{{ $payment->payment_method_name ?: strtoupper($payment->payment_method_code) }}</td><td class="px-5 py-4"><x-admin.order-status :status="$payment->payment_status" type="payment" />@if($payment->payment_method_code === 'cod' && in_array($payment->payment_status, ['unpaid','pending'], true))<p class="mt-1 text-xs font-bold text-rose-700">{{ __('admin.reports.cod_needs_collection') }}</p>@endif</td><td class="px-5 py-4 text-right">{{ $payment->orders_count }}</td><td class="px-5 py-4 text-right font-bold">{{ $service->formatBase($payment->amount) }}</td><td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $payment->last_payment }}</td></tr>@empty<tr><td colspan="5" class="px-6 py-14 text-center text-slate-500">{{ __('admin.reports.empty') }}</td></tr>@endforelse</tbody>
        </table></div>
        @if($payments->hasPages())<div class="border-t px-5 py-4">{{ $payments->links() }}</div>@endif
    </div>
@endsection
