@extends('layouts.public')

@php($brandName = trim((string) ($theme['brand_name'] ?? '')))

@section('title', $brandName ? ($brandName.' - '.__('storefront.home_meta_title')) : __('storefront.home_meta_title'))
@section('meta_description', $theme['hero_subtitle'] ?: __('storefront.meta_description'))

@section('content')
<div class="bg-[var(--theme-bg)] text-[var(--theme-text)]">
    @if($theme['hero_enabled'])
        <section class="relative min-h-[620px] overflow-hidden bg-slate-950 text-white">
            @if($heroImage = $themeService->imageUrl($theme['hero_image_path']))
                <img src="{{ $heroImage }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-slate-950/45 to-slate-950/10"></div>
            @else
                <div class="absolute inset-0" style="background: linear-gradient(135deg, var(--theme-secondary), var(--theme-primary));"></div>
            @endif
            <div class="relative mx-auto flex min-h-[620px] max-w-screen-2xl flex-col justify-center px-4 py-20 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    @if($brandName)
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-white/70">{{ $brandName }}</p>
                    @endif
                    <h1 class="mt-4 text-4xl font-black leading-tight tracking-tight sm:text-6xl">{{ $theme['hero_title'] }}</h1>
                    @if($theme['hero_subtitle'])
                        <p class="mt-5 max-w-2xl text-base font-semibold leading-8 text-white/80 sm:text-lg">{{ $theme['hero_subtitle'] }}</p>
                    @endif
                    @if($theme['hero_button_text'] && $theme['hero_button_url'])
                        <a href="{{ $theme['hero_button_url'] }}" class="mt-8 inline-flex rounded-xl px-6 py-3.5 text-sm font-extrabold text-white shadow-lg transition hover:opacity-90" style="background: var(--theme-button);">{{ $theme['hero_button_text'] }}</a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if($sections['promotion_banner'])
        <x-storefront.banner-list :banners="$homeBanners" :service="$bannerService" class="pt-8" />
    @endif

    @if($sections['featured_categories'] && $categories->isNotEmpty())
        <section id="categories" class="mx-auto max-w-screen-2xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-end justify-between gap-4">
                <div><p class="text-xs font-extrabold uppercase tracking-[0.2em]" style="color: var(--theme-primary);">{{ __('storefront.featured_categories') }}</p><h2 class="mt-2 text-2xl font-black text-slate-950">{{ __('storefront.shop_by_category') }}</h2></div>
                <a href="{{ route('products.index') }}" class="text-sm font-extrabold" style="color: var(--theme-link);">{{ __('storefront.view_all') }}</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($categories as $category)
                    <a href="{{ route('products.index', ['category' => $catalogService->categorySlug($category, $currentLanguage), 'language' => $currentLanguage->code, 'currency' => $currentCurrency->code]) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <p class="text-lg font-black text-slate-950">{{ $catalogService->categoryName($category, $currentLanguage) }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($sections['featured_products'] && $featuredProducts->isNotEmpty())
        @include('storefront.home_product_section', ['title' => __('storefront.featured_products'), 'eyebrow' => __('storefront.featured'), 'products' => $featuredProducts])
    @endif

    @if($sections['new_arrivals'] && $newArrivals->isNotEmpty())
        @include('storefront.home_product_section', ['title' => __('storefront.new_arrivals'), 'eyebrow' => __('storefront.latest'), 'products' => $newArrivals])
    @endif

    @if($sections['best_sellers'] && $bestSellers->isNotEmpty())
        @include('storefront.home_product_section', ['title' => __('storefront.best_sellers'), 'eyebrow' => __('storefront.popular'), 'products' => $bestSellers])
    @endif

    @if($sections['newsletter'])
        <section class="mx-auto max-w-screen-2xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="rounded-3xl p-8 text-white sm:p-10" style="background: linear-gradient(135deg, var(--theme-secondary), var(--theme-primary));">
                <h2 class="text-2xl font-black">{{ __('storefront.newsletter_title') }}</h2>
                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-white/80">{{ __('storefront.newsletter_intro') }}</p>
            </div>
        </section>
    @endif
</div>
@endsection
