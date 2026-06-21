@php
    $name = $catalogService->productName($product, $currentLanguage);
    $image = $catalogService->mainImage($product);
    $status = $catalogService->stockStatus($product);
    $discount = $catalogService->discountPercentage($product);
@endphp
<article class="group flex h-full flex-col overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-xl hover:shadow-indigo-100/60">
    <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-slate-100 to-slate-50">
        @if($image)
            <img src="{{ $catalogService->imageUrl($image) }}" alt="{{ $image->alt_text ?: $name }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden')">
            @include('storefront.products._placeholder', ['class' => 'hidden'])
        @else
            @include('storefront.products._placeholder')
        @endif
        <div class="absolute left-3 top-3 flex flex-wrap gap-2">@if($discount)<span class="rounded-full bg-rose-500 px-2.5 py-1 text-[11px] font-extrabold text-white shadow-sm">-{{ $discount }}%</span>@endif @if($product->is_featured)<span class="rounded-full bg-amber-400 px-2.5 py-1 text-[11px] font-extrabold text-amber-950 shadow-sm">{{ __('storefront.featured') }}</span>@endif</div>
        <span @class(['absolute bottom-3 right-3 rounded-full px-2.5 py-1 text-[11px] font-extrabold shadow-sm backdrop-blur','bg-emerald-100/95 text-emerald-700'=>$status==='in_stock','bg-amber-100/95 text-amber-700'=>$status==='low_stock','bg-rose-100/95 text-rose-700'=>$status==='out_of_stock'])>{{ __('storefront.'.$status) }}</span>
    </div>
    <div class="flex flex-1 flex-col p-5">
        <a href="{{ route('products.index', array_merge(request()->query(), ['category'=>$catalogService->categorySlug($product->category, $currentLanguage), 'page'=>null])) }}" class="mb-2 w-fit text-[11px] font-extrabold uppercase tracking-[0.14em] text-indigo-600 hover:text-indigo-800">{{ $catalogService->categoryName($product->category, $currentLanguage) }}</a>
        <h2 class="min-h-[3.5rem] text-lg font-extrabold leading-7 text-slate-950">{{ $name }}</h2>
        @if($description = $catalogService->shortDescription($product, $currentLanguage))<p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ $description }}</p>@endif
        <div class="mt-auto pt-5">
            @if($product->sale_price !== null && (float)$product->sale_price < (float)$product->price)
                <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1"><span class="text-xl font-extrabold tracking-tight text-rose-600">{{ $catalogService->formatPrice($product->sale_price, $currentCurrency, $baseCurrency) }}</span><span class="text-sm font-semibold text-slate-400 line-through">{{ $catalogService->formatPrice($product->price, $currentCurrency, $baseCurrency) }}</span></div>
            @else
                <span class="text-xl font-extrabold tracking-tight text-slate-950">{{ $catalogService->formatPrice($product->price, $currentCurrency, $baseCurrency) }}</span>
            @endif
            <button type="button" disabled class="mt-4 flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-slate-100 px-4 py-3 text-sm font-bold text-slate-500" title="{{ __('storefront.detail_coming') }}"><span>{{ __('storefront.view_product') }}</span><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg></button>
        </div>
    </div>
</article>
