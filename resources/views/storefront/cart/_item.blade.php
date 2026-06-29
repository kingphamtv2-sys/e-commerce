<article data-cart-row="{{ $item['id'] }}" class="grid gap-4 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 sm:grid-cols-[120px_minmax(0,1fr)] sm:p-5">
    <div class="aspect-square overflow-hidden rounded-2xl bg-slate-100">
        @if ($item['image_url'])
            <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
        @else
            @include('storefront.products._placeholder')
        @endif
    </div>
    <div class="min-w-0">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <h2 class="text-lg font-extrabold text-slate-950">{{ $item['name'] }}</h2>
                <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-400">{{ __('storefront.sku') }}: {{ $item['sku'] }}</p>
                @if ($item['variant_label'])
                    <p class="mt-2 text-sm font-semibold text-indigo-700">{{ $item['variant_label'] }}</p>
                @endif
                @if (! $item['available'])
                    <div data-cart-errors class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-bold text-rose-700">{{ $item['availability_message'] }}</div>
                @else
                    <div data-cart-errors class="mt-3 hidden rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-bold text-rose-700"></div>
                @endif
            </div>
            <div class="text-left lg:text-right">
                <p class="text-sm font-semibold text-slate-500">{{ __('storefront.unit_price') }}</p>
                <p class="mt-1 text-lg font-extrabold text-slate-950">{{ $item['formatted_unit_price'] }}</p>
            </div>
        </div>

        <div class="mt-5 flex flex-col gap-4 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('cart.items.update', $item['model']) }}" data-cart-update class="flex w-full max-w-[210px] items-center overflow-hidden rounded-2xl border border-slate-200 bg-white">
                @csrf @method('PATCH')
                <button type="button" data-cart-decrease class="h-12 w-12 text-xl font-bold text-slate-500 disabled:opacity-30" @disabled(! $item['available'] || $item['quantity'] <= 1)>−</button>
                <input data-cart-quantity name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ max(1, $item['available_quantity']) }}" type="number" class="h-12 min-w-0 flex-1 border-0 p-0 text-center font-extrabold text-slate-950 focus:ring-0" @disabled(! $item['available'])>
                <button type="button" data-cart-increase class="h-12 w-12 text-xl font-bold text-slate-500 disabled:opacity-30" @disabled(! $item['available'] || $item['quantity'] >= $item['available_quantity'])>+</button>
            </form>
            <div class="flex items-center justify-between gap-4 sm:ml-auto">
                <div class="text-right">
                    <p class="text-xs font-semibold text-slate-500">{{ __('storefront.item_subtotal') }}</p>
                    <p class="text-xl font-extrabold text-slate-950">{{ $item['formatted_subtotal'] }}</p>
                </div>
                <button type="button" data-cart-remove="{{ route('cart.items.destroy', $item['model']) }}" class="rounded-2xl border border-rose-200 px-4 py-2.5 text-sm font-extrabold text-rose-700 transition hover:bg-rose-50">{{ __('storefront.remove') }}</button>
            </div>
        </div>
    </div>
</article>
