@php
    $name = $productService->name($product);
    $image = $product->productImages->first();
    $variantCount = $product->productVariants->count();
    $availableStock = $productService->availableStock($product);
    $stockStatus = $productService->stockStatus($product);
    $deleteTarget = "[data-product-list-item='{$product->id}']";
@endphp
<article data-product-list-item="{{ $product->id }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex min-w-0 gap-3">
        <div class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 text-slate-400">
            @if($image)<img src="/storage/{{ ltrim($image->image_path, '/') }}" alt="{{ $image->alt_text ?: $name }}" loading="lazy" class="h-full w-full object-cover" onerror="this.remove(); this.parentElement.querySelector('[data-image-fallback]').classList.remove('hidden')">@endif
            <span data-image-fallback @class(['hidden' => $image !== null])><x-admin.icon name="image" /></span>
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <a href="{{ route('admin.products.edit', $product) }}" class="block truncate font-extrabold text-slate-950" title="{{ $name }}">{{ $name }}</a>
                    <code class="mt-1 block truncate text-xs font-bold text-slate-500">{{ $product->sku }}</code>
                </div>
                <span @class(['shrink-0 rounded-full px-2.5 py-1 text-[11px] font-extrabold', 'bg-emerald-50 text-emerald-700' => $product->status, 'bg-slate-100 text-slate-600' => ! $product->status])>{{ $product->status ? __('admin.common.active') : __('admin.common.inactive') }}</span>
            </div>
            <p class="mt-2 truncate text-xs font-semibold text-slate-500">{{ $categoryService->name($product->category) }}@if($variantCount) · {{ trans_choice('admin.products.variant_count', $variantCount, ['count' => $variantCount]) }}@endif</p>
        </div>
    </div>

    <dl class="mt-4 grid grid-cols-3 gap-2 border-y border-slate-100 py-3 text-sm">
        <div class="min-w-0"><dt class="text-[11px] font-bold uppercase text-slate-400">{{ __('admin.products.price') }}</dt><dd class="mt-1 truncate font-extrabold {{ $product->sale_price !== null ? 'text-rose-600' : 'text-slate-800' }}">{{ $defaultCurrency ? $currencyService->format((float) ($product->sale_price ?? $product->price), $defaultCurrency) : ($product->sale_price ?? $product->price) }}</dd></div>
        <div><dt class="text-[11px] font-bold uppercase text-slate-400">{{ __('admin.products.stock') }}</dt><dd class="mt-1 font-extrabold text-slate-800">{{ $availableStock }} <span @class(['text-[10px] font-bold', 'text-emerald-700' => $stockStatus === 'in_stock', 'text-amber-700' => $stockStatus === 'low_stock', 'text-rose-700' => $stockStatus === 'out_of_stock'])>{{ __('admin.inventory.'.$stockStatus) }}</span></dd></div>
        <div><dt class="text-[11px] font-bold uppercase text-slate-400">{{ __('admin.products.sort_short') }}</dt><dd class="mt-1 font-extrabold text-slate-800">{{ $sortPosition }}</dd></div>
    </dl>

    <div class="mt-4 flex items-center justify-between gap-3">
        <p class="text-xs text-slate-400">{{ __('admin.products.updated') }} {{ $product->updated_at?->format('Y-m-d H:i') }}</p>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white">{{ __('admin.common.edit') }}</a>
            <details class="relative">
                <summary class="cursor-pointer list-none rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600">{{ __('admin.products.more') }}</summary>
                <div class="absolute bottom-full right-0 z-30 mb-1 w-44 rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl">
                    <a href="{{ route('admin.inventory.index', ['keyword' => $product->sku]) }}" class="block rounded-lg px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">{{ __('admin.products.view_inventory') }}</a>
                    <div class="my-1 border-t border-slate-100"></div>
                    <button type="button" data-async-delete data-delete-url="{{ route('admin.products.destroy', $product) }}" data-delete-target="{{ $deleteTarget }}" data-delete-title="{{ __('admin.products.delete_title') }}" data-delete-message="{{ __('admin.products.delete_message', ['name' => $name]) }}" data-delete-warning="{{ __('admin.products.delete_warning') }}" class="w-full rounded-lg px-3 py-2 text-left text-xs font-bold text-rose-700 hover:bg-rose-50">{{ __('admin.common.delete') }}</button>
                </div>
            </details>
        </div>
    </div>
</article>
