@extends('layouts.admin')
@section('title', __('admin.tax_classes.title'))
@section('page-actions')
    <a href="{{ route('admin.tax-classes.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">+ {{ __('admin.tax_classes.add') }}</a>
@endsection
@section('content')
    @if (session('success')) <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">{{ $errors->first() }}</div> @endif
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.tax_classes.list') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('admin.tax_classes.list_desc') }}</p></div>
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200">
            <thead><tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500"><th class="px-6 py-4">{{ __('admin.tax_classes.code') }}</th><th class="px-4 py-4">{{ __('admin.tax_classes.name') }}</th><th class="px-4 py-4">{{ __('admin.tax_classes.description') }}</th><th class="px-4 py-4">{{ __('admin.tax_classes.rates') }}</th><th class="px-4 py-4">{{ __('admin.common.active') }}</th><th class="px-6 py-4 text-right">{{ __('admin.common.actions') }}</th></tr></thead>
            <tbody class="divide-y divide-slate-100">@forelse ($taxClasses as $taxClass)
                <tr class="hover:bg-slate-50"><td class="px-6 py-4"><code class="text-xs font-bold text-slate-600">{{ $taxClass->code }}</code></td><td class="px-4 py-4 font-bold text-slate-900">{{ $taxClass->name }}</td><td class="px-4 py-4 text-sm text-slate-600">{{ $taxClass->description ?: '—' }}</td><td class="px-4 py-4 text-sm font-semibold text-slate-600">{{ $taxClass->tax_rates_count }}</td><td class="px-4 py-4"><span @class(['rounded-full px-2.5 py-1 text-xs font-bold', 'bg-emerald-50 text-emerald-700' => $taxClass->status, 'bg-slate-100 text-slate-500' => ! $taxClass->status])>{{ $taxClass->status ? __('admin.common.active') : __('admin.common.inactive') }}</span></td>
                <td class="px-6 py-4"><div class="flex justify-end gap-2"><a href="{{ route('admin.tax-classes.edit', $taxClass) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700">{{ __('admin.common.edit') }}</a><form method="POST" action="{{ route('admin.tax-classes.destroy', $taxClass) }}" onsubmit="return confirm(@js(__('admin.tax_classes.delete_confirm')))" >@csrf @method('DELETE')<button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-700">{{ __('admin.common.delete') }}</button></form></div></td></tr>
            @empty <tr><td colspan="6" class="px-6 py-16 text-center text-sm text-slate-500">{{ __('admin.tax_classes.empty') }}</td></tr>@endforelse</tbody>
        </table></div>
    </div>
@endsection
