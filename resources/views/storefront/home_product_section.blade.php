<section class="mx-auto max-w-screen-2xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-end justify-between gap-4">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.2em]" style="color: var(--theme-primary);">{{ $eyebrow }}</p>
            <h2 class="mt-2 text-2xl font-black text-slate-950">{{ $title }}</h2>
        </div>
        <a href="{{ route('products.index') }}" class="text-sm font-extrabold" style="color: var(--theme-link);">{{ __('storefront.view_all') }}</a>
    </div>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($products as $product)
            @include('storefront.products._card', ['product' => $product])
        @endforeach
    </div>
</section>
