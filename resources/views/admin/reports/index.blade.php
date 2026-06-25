@extends('layouts.admin')
@section('title', __('admin.reports.title'))
@section('content')
    <p class="mb-6 max-w-3xl text-sm leading-6 text-slate-500">{{ __('admin.reports.index_intro') }}</p>
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach([
            ['sales', 'banknote', 'bg-emerald-50 text-emerald-700'],
            ['orders', 'shopping-bag', 'bg-indigo-50 text-indigo-700'],
            ['product-sales', 'cube', 'bg-violet-50 text-violet-700'],
            ['inventory', 'archive', 'bg-amber-50 text-amber-700'],
            ['coupons', 'ticket', 'bg-rose-50 text-rose-700'],
            ['taxes', 'percent', 'bg-sky-50 text-sky-700'],
            ['payments', 'receipt', 'bg-emerald-50 text-emerald-700'],
        ] as [$report, $icon, $tone])
            <a href="{{ route('admin.reports.'.$report) }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                <span class="grid h-12 w-12 place-items-center rounded-xl {{ $tone }}"><x-admin.icon :name="$icon" /></span>
                <h2 class="mt-5 text-lg font-extrabold text-slate-950">{{ __("admin.reports.{$report}_title") }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">{{ __("admin.reports.{$report}_description") }}</p>
                <span class="mt-5 inline-flex text-sm font-bold text-indigo-700">{{ __('admin.reports.view_report') }} →</span>
            </a>
        @endforeach
    </div>
@endsection
