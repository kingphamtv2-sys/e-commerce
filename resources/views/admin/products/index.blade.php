@extends('layouts.admin')
@section('title', __('admin.products.title'))
@section('page-actions')
    <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700">
        <span aria-hidden="true">+</span> {{ __('admin.products.add') }}
    </a>
@endsection
@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">{{ $errors->first() }}</div>
    @endif

    <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-3 xl:grid-cols-6">
        <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="{{ __('admin.products.search') }}" class="rounded-xl border-slate-300 text-sm">
        <select name="category_id" class="rounded-xl border-slate-300 text-sm">
            <option value="">{{ __('admin.products.all_categories') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) ($filters['category_id'] ?? 0) === $category->id)>{{ $categoryService->name($category) }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-xl border-slate-300 text-sm">
            <option value="">{{ __('admin.products.all_statuses') }}</option>
            <option value="1" @selected(($filters['status'] ?? '') === '1')>{{ __('admin.common.active') }}</option>
            <option value="0" @selected(($filters['status'] ?? '') === '0')>{{ __('admin.common.inactive') }}</option>
        </select>
        <select name="is_featured" class="rounded-xl border-slate-300 text-sm">
            <option value="">{{ __('admin.products.all_featured') }}</option>
            <option value="1" @selected(($filters['is_featured'] ?? '') === '1')>{{ __('admin.products.featured') }}</option>
            <option value="0" @selected(($filters['is_featured'] ?? '') === '0')>{{ __('admin.products.normal') }}</option>
        </select>
        <select name="sort" class="rounded-xl border-slate-300 text-sm">
            <option value="newest">{{ __('admin.products.newest') }}</option>
            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('admin.products.price_asc') }}</option>
            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('admin.products.price_desc') }}</option>
        </select>
        <div class="flex gap-2">
            <button class="flex-1 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white">{{ __('admin.products.filter') }}</button>
            <a href="{{ route('admin.products.index') }}" class="rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-bold text-slate-600">{{ __('admin.products.reset') }}</a>
        </div>
    </form>

    @if($products->isEmpty())
        @php($hasFilters = collect($filters)->contains(fn ($value) => $value !== null && $value !== '' && $value !== 'newest'))
        <section class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm">
            <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-indigo-50 text-indigo-600">
                <x-admin.icon name="cube" />
            </span>
            <h2 class="mt-5 text-lg font-extrabold text-slate-950">{{ __('admin.products.'.($hasFilters ? 'empty_filtered_title' : 'empty_title')) }}</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">{{ __('admin.products.'.($hasFilters ? 'empty_filtered_description' : 'empty_description')) }}</p>
            <a href="{{ $hasFilters ? route('admin.products.index') : route('admin.products.create') }}" class="mt-6 inline-flex rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">{{ $hasFilters ? __('admin.products.reset') : __('admin.products.add') }}</a>
        </section>
    @else
        <section class="hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:block">
            <table class="w-full table-fixed">
                <colgroup>
                    <col class="w-[72px]"><col><col class="w-[14%]"><col class="w-[14%]"><col class="w-[9%]">
                    <col class="w-[10%]"><col class="w-[6%]"><col class="w-[11%]"><col class="w-[136px]">
                </colgroup>
                <thead class="border-b border-slate-200 bg-slate-50/80">
                    <tr class="text-left text-[11px] font-extrabold uppercase tracking-[0.12em] text-slate-500">
                        <th class="px-4 py-4">{{ __('admin.products.image') }}</th>
                        <th class="px-4 py-4">{{ __('admin.products.product') }}</th>
                        <th class="px-4 py-4">{{ __('admin.products.category') }}</th>
                        <th class="px-4 py-4">{{ __('admin.products.price') }}</th>
                        <th class="px-4 py-4">{{ __('admin.products.stock') }}</th>
                        <th class="px-4 py-4">{{ __('admin.products.status') }}</th>
                        <th class="px-2 py-4 text-center">{{ __('admin.products.sort_short') }}</th>
                        <th class="px-4 py-4">{{ __('admin.products.updated') }}</th>
                        <th class="px-4 py-4 text-right">{{ __('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($products as $product)
                        @include('admin.products.partials.list-row', [
                            'sortPosition' => ($products->firstItem() ?? 1) + $loop->index,
                        ])
                    @endforeach
                </tbody>
            </table>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 lg:hidden">
            @foreach ($products as $product)
                @include('admin.products.partials.list-card', [
                    'sortPosition' => ($products->firstItem() ?? 1) + $loop->index,
                ])
            @endforeach
        </div>

        @if($products->hasPages())
            <div class="mt-5 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">{{ $products->links() }}</div>
        @endif
    @endif

    @include('admin.products.partials.delete-modal')
@endsection
