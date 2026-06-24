@extends('layouts.public')

@section('title', ($translation->meta_title ?: $translation->name).' — '.$siteName)
@section('meta_description', $translation->meta_description ?: ($translation->short_description ?: __('storefront.meta_description')))

@php
    $images = $product->productImages->map(fn ($image) => [
        'url' => $catalogService->imageUrl($image),
        'alt' => $image->alt_text ?: $translation->name,
    ])->values();
    $stockStatus = $catalogService->stockStatus($product);
    $discount = $catalogService->discountPercentage($product);
    $categoryName = $catalogService->categoryName($product->category, $currentLanguage);
    $categorySlug = $catalogService->categorySlug($product->category, $currentLanguage);
@endphp

@section('content')
<div class="bg-slate-50" x-data="{
    activeImage: 0,
    imageFailed: false,
    productImages: @js($images),
    quantity: 1,
    variants: @js($variantOptions),
    selectedId: null,
    selectedValues: {},
    optionCount: @js($productOptions->count()),
    basePrice: @js($catalogService->formatPrice($product->sale_price ?? $product->price, $currentCurrency, $baseCurrency)),
    baseOriginalPrice: @js($product->sale_price !== null && (float) $product->sale_price < (float) $product->price ? $catalogService->formatPrice($product->price, $currentCurrency, $baseCurrency) : null),
    baseSku: @js($product->sku),
    baseStatus: @js($stockStatus),
    baseAvailable: @js($availableQuantity),
    stockLabels: @js(['in_stock' => __('storefront.in_stock'), 'low_stock' => __('storefront.low_stock'), 'out_of_stock' => __('storefront.out_of_stock')]),
    get selectedVariant() {
        if (this.optionCount > 0) {
            const chosen = Object.values(this.selectedValues).map(Number);
            if (chosen.length !== this.optionCount) return null;
            return this.variants.find(variant => variant.option_value_ids.length === chosen.length && variant.option_value_ids.every(id => chosen.includes(Number(id)))) ?? null;
        }
        return this.variants.find(variant => variant.id === this.selectedId) ?? null;
    },
    get currentStatus() { return this.selectedVariant?.stock_status ?? this.baseStatus },
    get currentAvailable() { return this.selectedVariant?.available_quantity ?? this.baseAvailable },
    get currentImages() { return this.selectedVariant?.images?.length ? this.selectedVariant.images : this.productImages },
    get maxQuantity() { return Math.max(1, this.currentAvailable) },
    chooseVariant(id) { this.selectedId = id; this.quantity = 1; this.activeImage = 0; this.imageFailed = false },
    chooseOption(optionId, valueId) { this.selectedValues[optionId] = valueId; this.quantity = 1; this.activeImage = 0; this.imageFailed = false },
    chooseImage(index) { this.activeImage = index; this.imageFailed = false },
    decrease() { this.quantity = Math.max(1, this.quantity - 1) },
    increase() { this.quantity = Math.min(this.maxQuantity, this.quantity + 1) },
}">
    <div class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-screen-2xl flex-wrap items-center gap-2 px-4 py-5 text-sm text-slate-500 sm:px-6 lg:px-8" aria-label="{{ __('storefront.breadcrumb') }}">
            <a href="{{ route('home') }}" class="hover:text-indigo-600">{{ __('storefront.home') }}</a><span>/</span>
            <a href="{{ route('products.index') }}" class="hover:text-indigo-600">{{ __('storefront.products') }}</a><span>/</span>
            <a href="{{ route('products.index', ['category' => $categorySlug, 'language' => $currentLanguage->code, 'currency' => $currentCurrency->code]) }}" class="hover:text-indigo-600">{{ $categoryName }}</a><span>/</span>
            <span class="max-w-xs truncate font-semibold text-slate-900">{{ $translation->name }}</span>
        </nav>
    </div>
    <x-storefront.banner-list :banners="$productBanners" :service="$bannerService" />

    <section class="mx-auto max-w-screen-2xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <div class="grid items-start gap-8 lg:grid-cols-2 xl:gap-14">
            <div class="lg:sticky lg:top-28">
                <div class="relative aspect-square overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <template x-if="currentImages.length > 0 && !imageFailed"><img :src="currentImages[activeImage]?.url" :alt="currentImages[activeImage]?.alt" x-on:error="imageFailed = true" class="absolute inset-0 h-full w-full object-contain p-5 sm:p-8" fetchpriority="high"></template>
                    <div x-show="currentImages.length === 0 || imageFailed" class="absolute inset-0">@include('storefront.products._placeholder')</div>
                    @if ($discount)
                        <span class="absolute left-5 top-5 rounded-full bg-rose-500 px-3 py-1.5 text-sm font-extrabold text-white shadow-lg">-{{ $discount }}%</span>
                    @endif
                </div>

                <div x-show="currentImages.length > 1" class="mt-4 grid grid-cols-5 gap-3 sm:grid-cols-6" aria-label="{{ __('storefront.image_gallery') }}">
                    <template x-for="(image, index) in currentImages" :key="image.url"><button type="button" @click="chooseImage(index)" :class="activeImage === index ? 'border-indigo-600 ring-2 ring-indigo-100' : 'border-slate-200 hover:border-indigo-300'" class="aspect-square overflow-hidden rounded-2xl border bg-white p-1.5 transition"><img :src="image.url" :alt="image.alt" class="h-full w-full rounded-xl object-cover" loading="lazy"></button></template>
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8 lg:p-10">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('products.index', ['category' => $categorySlug]) }}" class="text-xs font-extrabold uppercase tracking-[0.18em] text-indigo-600 hover:text-indigo-800">{{ $categoryName }}</a>
                    @if ($product->is_featured)<span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-800">{{ __('storefront.featured') }}</span>@endif
                </div>
                <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-tight text-slate-950 sm:text-4xl xl:text-5xl">{{ $translation->name }}</h1>

                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
                    <span class="text-slate-500">{{ __('storefront.sku') }}: <strong class="font-bold text-slate-800" x-text="selectedVariant?.sku ?? baseSku"></strong></span>
                    <span class="h-4 w-px bg-slate-200"></span>
                    <span class="inline-flex items-center gap-2 font-bold" :class="{'text-emerald-700': currentStatus === 'in_stock', 'text-amber-700': currentStatus === 'low_stock', 'text-rose-700': currentStatus === 'out_of_stock'}">
                        <span class="h-2 w-2 rounded-full bg-current"></span><span x-text="stockLabels[currentStatus]"></span>
                    </span>
                </div>

                @if ($translation->short_description)
                    <p class="mt-6 text-base leading-7 text-slate-600">{{ $translation->short_description }}</p>
                @endif

                <div class="mt-7 rounded-2xl bg-slate-50 p-5">
                    <div class="flex flex-wrap items-end gap-3">
                        <span class="text-3xl font-extrabold tracking-tight text-slate-950" x-text="selectedVariant?.price ?? basePrice"></span>
                        <span x-show="selectedVariant?.original_price ?? baseOriginalPrice" class="pb-1 text-base font-semibold text-slate-400 line-through" x-text="selectedVariant?.original_price ?? baseOriginalPrice"></span>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">{{ __('storefront.price_currency_note', ['currency' => $currentCurrency->code]) }}</p>
                </div>

                @if ($productOptions->isNotEmpty())
                    <div class="mt-8">
                        <div class="flex items-center justify-between gap-4">
                            <h2 class="text-sm font-extrabold text-slate-950">{{ __('storefront.choose_variant') }}</h2>
                            <span x-show="!selectedVariant" class="text-xs font-semibold text-amber-600">{{ __('storefront.variant_required') }}</span>
                        </div>
                        <div class="mt-4 space-y-5">
                            @foreach ($productOptions as $option)
                                <div>
                                    <p class="mb-2 text-sm font-bold text-slate-800">{{ $option->label() }}</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($option->values as $value)
                                            <button type="button" @click="chooseOption({{ $option->id }}, {{ $value->id }})" :class="Number(selectedValues[{{ $option->id }}]) === {{ $value->id }} ? 'border-indigo-600 bg-indigo-50 text-indigo-700 ring-2 ring-indigo-100' : 'border-slate-200 bg-white text-slate-700 hover:border-indigo-300'" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold transition">
                                                @if ($value->color_code)<span class="h-4 w-4 rounded-full border border-slate-300" style="background-color: {{ $value->color_code }}"></span>@endif
                                                {{ $value->label() }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif ($variantOptions !== [])
                    <div class="mt-8">
                        <div class="flex items-center justify-between gap-4"><h2 class="text-sm font-extrabold text-slate-950">{{ __('storefront.choose_variant') }}</h2><span x-show="!selectedVariant" class="text-xs font-semibold text-amber-600">{{ __('storefront.variant_required') }}</span></div>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">@foreach ($variantOptions as $variant)<button type="button" @click="chooseVariant({{ $variant['id'] }})" :class="selectedId === {{ $variant['id'] }} ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-100' : 'border-slate-200 bg-white hover:border-indigo-300'" class="flex items-center justify-between rounded-2xl border px-4 py-3 text-left transition"><span><span class="block text-sm font-bold text-slate-900">{{ $variant['name'] }}</span><span class="mt-0.5 block text-xs text-slate-500">{{ $variant['sku'] }}</span></span><span @class(['text-xs font-bold', 'text-rose-600' => $variant['stock_status'] === 'out_of_stock', 'text-slate-500' => $variant['stock_status'] !== 'out_of_stock'])>{{ __('storefront.'.$variant['stock_status']) }}</span></button>@endforeach</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('cart.items.store') }}" data-cart-add class="mt-8">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="product_variant_id" :value="selectedVariant?.id ?? ''">
                    <input type="hidden" name="quantity" :value="quantity">
                    <div data-cart-errors class="mb-4 hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700"></div>
                    <div class="grid gap-4 sm:grid-cols-[150px_minmax(0,1fr)]">
                        <div>
                            <label class="mb-2 block text-sm font-extrabold text-slate-950">{{ __('storefront.quantity') }}</label>
                            <div class="flex h-14 items-center overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                <button type="button" @click="decrease()" :disabled="quantity <= 1 || currentStatus === 'out_of_stock'" class="h-full w-12 text-xl font-semibold text-slate-500 disabled:opacity-30" aria-label="{{ __('storefront.decrease_quantity') }}">−</button>
                                <input type="number" x-model.number="quantity" min="1" :max="maxQuantity" :disabled="currentStatus === 'out_of_stock'" @change="quantity = Math.min(maxQuantity, Math.max(1, Number(quantity) || 1))" class="h-full min-w-0 flex-1 border-0 p-0 text-center font-extrabold text-slate-900 focus:ring-0 disabled:bg-white">
                                <button type="button" @click="increase()" :disabled="quantity >= maxQuantity || currentStatus === 'out_of_stock'" class="h-full w-12 text-xl font-semibold text-slate-500 disabled:opacity-30" aria-label="{{ __('storefront.increase_quantity') }}">+</button>
                            </div>
                        </div>
                        <div class="self-end">
                            <button type="submit" data-loading-label="{{ __('storefront.adding_to_cart') }}" :disabled="currentStatus === 'out_of_stock' || (variants.length > 0 && !selectedVariant)" class="flex h-14 w-full items-center justify-center gap-3 rounded-2xl bg-slate-950 px-6 text-base font-extrabold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-slate-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 7h15l-2 8H8L6 3H3"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                                <span data-cart-button-label x-text="currentStatus === 'out_of_stock' ? stockLabels.out_of_stock : (variants.length > 0 && !selectedVariant ? @js(__('storefront.variant_required')) : @js(__('storefront.add_to_cart')))"></span>
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-8 grid grid-cols-3 gap-3 border-t border-slate-100 pt-6 text-center text-xs font-bold text-slate-600">
                    <div class="rounded-xl bg-slate-50 px-2 py-3">{{ __('storefront.secure_shopping') }}</div>
                    <div class="rounded-xl bg-slate-50 px-2 py-3">{{ __('storefront.quality_assured') }}</div>
                    <div class="rounded-xl bg-slate-50 px-2 py-3">{{ __('storefront.support_ready') }}</div>
                </div>
            </div>
        </div>
    </section>

    @if ($translation->description)
        <section class="border-y border-slate-200 bg-white">
            <div class="mx-auto max-w-screen-2xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-indigo-600">{{ __('storefront.product_information') }}</p>
                <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('storefront.description') }}</h2>
                <div class="mt-6 max-w-4xl whitespace-pre-line text-base leading-8 text-slate-600">{{ $translation->description }}</div>
            </div>
        </section>
    @endif

    @if ($relatedProducts->isNotEmpty())
        <section class="mx-auto max-w-screen-2xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <div class="mb-7 flex items-end justify-between gap-4">
                <div><p class="text-xs font-extrabold uppercase tracking-[0.2em] text-indigo-600">{{ __('storefront.you_may_like') }}</p><h2 class="mt-2 text-2xl font-extrabold text-slate-950 sm:text-3xl">{{ __('storefront.related_products') }}</h2></div>
                <a href="{{ route('products.index', ['category' => $categorySlug]) }}" class="hidden text-sm font-bold text-indigo-600 hover:text-indigo-800 sm:block">{{ __('storefront.view_all') }} →</a>
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($relatedProducts as $relatedProduct)
                    @include('storefront.products._card', ['product' => $relatedProduct])
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
