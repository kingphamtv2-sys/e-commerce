@extends('layouts.admin')

@section('title', __('admin.coupons.title'))

@section('page-actions')
    <a href="{{ route('admin.coupons.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700">
        <span class="text-lg leading-none">+</span> {{ __('admin.coupons.add') }}
    </a>
@endsection

@section('content')
<div x-data="{ openDelete: false, deleteUrl: '', deleteCode: '' }">
    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">{{ $errors->first() }}</div>
    @endif

    <form method="GET" action="{{ route('admin.coupons.index') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[minmax(0,1fr)_180px_180px_auto]">
        <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="{{ __('admin.coupons.search') }}" class="rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <select name="status" class="rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('admin.coupons.all_statuses') }}</option>
            <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('admin.common.active') }}</option>
            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>{{ __('admin.common.inactive') }}</option>
        </select>
        <select name="discount_type" class="rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('admin.coupons.all_types') }}</option>
            <option value="percentage" @selected(($filters['discount_type'] ?? '') === 'percentage')>{{ __('admin.coupons.percentage') }}</option>
            <option value="fixed_amount" @selected(($filters['discount_type'] ?? '') === 'fixed_amount')>{{ __('admin.coupons.fixed_amount') }}</option>
        </select>
        <div class="flex gap-2">
            <button class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-800">{{ __('admin.coupons.filter') }}</button>
            <a href="{{ route('admin.coupons.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">{{ __('admin.coupons.reset') }}</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
            <h2 class="font-bold text-slate-950">{{ __('admin.coupons.list') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('admin.coupons.list_desc') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-white">
                    <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">{{ __('admin.coupons.code') }}</th>
                        <th class="px-4 py-4">{{ __('admin.coupons.type') }}</th>
                        <th class="px-4 py-4">{{ __('admin.coupons.min_order') }}</th>
                        <th class="px-4 py-4">{{ __('admin.coupons.usage') }}</th>
                        <th class="px-4 py-4">{{ __('admin.coupons.date_range') }}</th>
                        <th class="px-4 py-4">{{ __('admin.coupons.status') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($coupons as $coupon)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <p class="font-mono text-sm font-black text-slate-950">{{ $coupon->code }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ $coupon->name ?: '—' }}</p>
                                <p class="mt-1 text-[11px] font-bold text-slate-400">{{ $coupon->products_count }} {{ __('admin.coupons.products') }} · {{ $coupon->categories_count }} {{ __('admin.coupons.categories') }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm font-bold text-slate-700">
                                {{ $coupon->discount_type === 'percentage' ? __('admin.coupons.percentage') : __('admin.coupons.fixed_amount') }}
                                <span class="block font-mono text-xs text-slate-500">
                                    {{ $coupon->discount_type === 'percentage' ? rtrim(rtrim($coupon->discount_value, '0'), '.').'%' : number_format((float) $coupon->discount_value) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-600">{{ $coupon->min_order_amount ? number_format((float) $coupon->min_order_amount) : __('admin.coupons.no_limit') }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-600">{{ $coupon->used_count }} / {{ $coupon->usage_limit ?: '∞' }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-xs font-semibold text-slate-500">
                                {{ $coupon->starts_at?->format('Y-m-d H:i') ?: '—' }}<br>
                                {{ $coupon->ends_at?->format('Y-m-d H:i') ?: '—' }}
                            </td>
                            <td class="px-4 py-4">
                                <span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-bold', 'bg-emerald-50 text-emerald-700' => $coupon->status === 'active', 'bg-slate-100 text-slate-500' => $coupon->status !== 'active'])>
                                    {{ $coupon->status === 'active' ? __('admin.common.active') : __('admin.common.inactive') }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50">{{ __('admin.common.edit') }}</a>
                                    <button type="button" @click="openDelete = true; deleteUrl = @js(route('admin.coupons.destroy', $coupon)); deleteCode = @js($coupon->code)" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-50">{{ __('admin.common.delete') }}</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-16 text-center text-sm text-slate-500">{{ __('admin.coupons.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $coupons->links() }}</div>
    </div>

    <div x-show="openDelete" x-transition.opacity class="fixed inset-0 z-[90] grid place-items-center bg-slate-950/60 p-4 backdrop-blur-sm" style="display: none;">
        <div @click.outside="openDelete = false" x-transition class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
            <div class="grid h-12 w-12 place-items-center rounded-2xl bg-rose-100 text-rose-700">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>
            </div>
            <h2 class="mt-4 text-xl font-extrabold text-slate-950">{{ __('admin.common.delete') }} <span x-text="deleteCode"></span>?</h2>
            <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">{{ __('admin.coupons.delete_warning') }}</p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="openDelete = false" class="rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-extrabold text-slate-700 hover:bg-slate-50">{{ __('admin.common.cancel') }}</button>
                <form method="POST" x-bind:action="deleteUrl">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-rose-700">{{ __('admin.common.delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
