@extends('layouts.admin')

@section('title', __('admin.dashboard.title'))

@section('content')
    @php
        $stats = [
            ['label' => __('admin.dashboard.total_orders'), 'value' => '0', 'hint' => 'No orders recorded yet', 'tone' => 'indigo'],
            ['label' => __('admin.dashboard.total_revenue'), 'value' => '₫0', 'hint' => 'Revenue will appear here', 'tone' => 'emerald'],
            ['label' => __('admin.dashboard.total_products'), 'value' => '0', 'hint' => 'Your catalog is ready', 'tone' => 'amber'],
            ['label' => __('admin.dashboard.low_stock'), 'value' => '0', 'hint' => 'Inventory looks healthy', 'tone' => 'rose'],
        ];
    @endphp

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Dashboard overview">
        @foreach ($stats as $stat)
            <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div @class([
                    'absolute -right-6 -top-6 h-24 w-24 rounded-full opacity-15',
                    'bg-indigo-500' => $stat['tone'] === 'indigo',
                    'bg-emerald-500' => $stat['tone'] === 'emerald',
                    'bg-amber-500' => $stat['tone'] === 'amber',
                    'bg-rose-500' => $stat['tone'] === 'rose',
                ])></div>
                <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                <p class="mt-3 text-3xl font-bold tracking-tight text-slate-950">{{ $stat['value'] }}</p>
                <p class="mt-2 text-xs text-slate-400">{{ $stat['hint'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">{{ __('admin.dashboard.revenue_overview') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('admin.dashboard.analytics_later') }}</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">{{ __('admin.common.placeholder') }}</span>
            </div>
            <div class="mt-6 flex h-64 items-end gap-3 rounded-xl border border-dashed border-slate-200 bg-slate-50/70 p-5">
                @foreach ([36, 54, 42, 68, 47, 76, 58, 82, 64, 88, 72, 94] as $height)
                    <div class="flex-1 rounded-t-md bg-indigo-200" style="height: {{ $height }}%"></div>
                @endforeach
            </div>
        </article>

        <article class="rounded-2xl bg-slate-950 p-6 text-white shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-300">{{ __('admin.dashboard.welcome_back') }}</p>
            <h2 class="mt-3 text-xl font-bold">{{ auth()->user()->name }}</h2>
            <p class="mt-2 text-sm leading-6 text-slate-400">{{ __('admin.dashboard.workspace_ready') }}</p>

            <div class="mt-8 space-y-3 border-t border-white/10 pt-5 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">{{ __('admin.dashboard.current_role') }}</span>
                    <span class="font-semibold">{{ str(auth()->user()->role)->replace('_', ' ')->title() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">{{ __('admin.dashboard.account_status') }}</span>
                    <span class="inline-flex items-center gap-2 font-semibold text-emerald-300">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>{{ __('admin.common.active') }}
                    </span>
                </div>
            </div>
        </article>
    </section>
@endsection
