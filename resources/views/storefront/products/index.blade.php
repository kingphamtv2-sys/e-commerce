@extends('layouts.public')

@section('title', ($selectedCategory ? $catalogService->categoryName($selectedCategory, $currentLanguage) : __('storefront.products')).' — '.$siteName)
@section('meta_description', __('storefront.catalog_meta'))

@section('content')
    <div x-data="{ filtersOpen: false }" class="min-h-screen bg-slate-50">
        <section class="relative overflow-hidden border-b border-slate-200 bg-white">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(79,70,229,0.12),_transparent_35%),radial-gradient(circle_at_bottom_left,_rgba(14,165,233,0.10),_transparent_30%)]"></div>
            <div class="relative mx-auto max-w-screen-2xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
                <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500" aria-label="{{ __('storefront.breadcrumb') }}">
                    <a href="{{ route('home') }}" class="transition hover:text-indigo-600">{{ __('storefront.home') }}</a>
                    <span aria-hidden="true">/</span>
                    <span class="font-medium text-slate-900">{{ $selectedCategory ? $catalogService->categoryName($selectedCategory, $currentLanguage) : __('storefront.products') }}</span>
                </nav>

                <div class="max-w-3xl">
                    <p class="mb-3 text-xs font-bold uppercase tracking-[0.24em] text-indigo-600">{{ __('storefront.catalog_eyebrow') }}</p>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl lg:text-5xl">
                        {{ $selectedCategory ? $catalogService->categoryName($selectedCategory, $currentLanguage) : __('storefront.catalog_title') }}
                    </h1>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">{{ __('storefront.catalog_intro') }}</p>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-screen-2xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ trans_choice('storefront.result_count', $products->total(), ['count' => $products->total()]) }}</p>
                    @if (! empty($filters['keyword']))
                        <p class="mt-1 text-sm text-slate-500">{{ __('storefront.searching_for', ['keyword' => $filters['keyword']]) }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" @click="filtersOpen = true" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-indigo-300 hover:text-indigo-600 lg:hidden">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 4.5h18M6.5 12h11M10 19.5h4"/></svg>
                        {{ __('storefront.filters') }}
                    </button>

                    <form method="GET" action="{{ route('products.index') }}" class="flex items-center gap-2">
                        @foreach (request()->except(['sort', 'page']) as $key => $value)
                            @if (is_scalar($value))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label for="catalog-sort" class="sr-only">{{ __('storefront.sort') }}</label>
                        <select id="catalog-sort" name="sort" onchange="this.form.submit()" class="rounded-xl border-slate-300 py-2.5 pl-3 pr-9 text-sm font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>{{ __('storefront.sort_newest') }}</option>
                            <option value="featured" @selected(($filters['sort'] ?? '') === 'featured')>{{ __('storefront.sort_featured') }}</option>
                            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('storefront.sort_price_asc') }}</option>
                            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('storefront.sort_price_desc') }}</option>
                            <option value="name_asc" @selected(($filters['sort'] ?? '') === 'name_asc')>{{ __('storefront.sort_name') }}</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-[280px_minmax(0,1fr)]">
                <aside class="hidden lg:block">
                    <div class="sticky top-28 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        @include('storefront.products._filters')
                    </div>
                </aside>

                <main class="min-w-0">
                    @if ($products->isNotEmpty())
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                            @foreach ($products as $product)
                                @include('storefront.products._card', ['product' => $product])
                            @endforeach
                        </div>

                        @if ($products->hasPages())
                            <div class="mt-10 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                                {{ $products->onEachSide(1)->links() }}
                            </div>
                        @endif
                    @else
                        <div class="flex min-h-[420px] flex-col items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="m3 3 18 18M10.6 10.6A2 2 0 0 0 13.4 13.4M9.9 4.24A9.9 9.9 0 0 1 12 4c5.5 0 9 8 9 8a16.8 16.8 0 0 1-2.1 3.25M6.2 6.2C4.1 8 3 12 3 12s3.5 8 9 8a8.8 8.8 0 0 0 4.2-1.1"/></svg>
                            </div>
                            <h2 class="mt-5 text-xl font-bold text-slate-950">{{ __('storefront.empty_title') }}</h2>
                            <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">{{ __('storefront.empty_description') }}</p>
                            <a href="{{ route('products.index', ['language' => $currentLanguage->code, 'currency' => $currentCurrency->code]) }}" class="mt-6 inline-flex items-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-700">
                                {{ __('storefront.reset_filters') }}
                            </a>
                        </div>
                    @endif
                </main>
            </div>
        </div>

        <div x-cloak x-show="filtersOpen" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="{{ __('storefront.filters') }}">
            <div x-show="filtersOpen" x-transition.opacity @click="filtersOpen = false" class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm"></div>
            <aside x-show="filtersOpen" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="absolute inset-y-0 right-0 w-full max-w-sm overflow-y-auto bg-white p-6 shadow-2xl">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-950">{{ __('storefront.filters') }}</h2>
                    <button type="button" @click="filtersOpen = false" class="rounded-xl p-2 text-slate-500 hover:bg-slate-100" aria-label="{{ __('storefront.close') }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>
                @include('storefront.products._filters')
            </aside>
        </div>
    </div>
@endsection
