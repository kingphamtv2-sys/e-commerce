@php
    $name = $productService->name($product);
    $image = $product->productImages->first();
    $variantCount = $product->productVariants->count();
    $availableStock = $productService->availableStock($product);
    $stockStatus = $productService->stockStatus($product);
    $deleteTarget = "[data-product-list-item='{$product->id}']";
@endphp
<tr data-product-list-item="{{ $product->id }}" class="group hover:bg-slate-50/80">
    <td class="px-4 py-4">
        <div class="grid h-12 w-12 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 text-slate-400">
            @if($image)
                <img src="/storage/{{ ltrim($image->image_path, '/') }}" alt="{{ $image->alt_text ?: $name }}" loading="lazy" class="h-full w-full object-cover" onerror="this.remove(); this.parentElement.querySelector('[data-image-fallback]').classList.remove('hidden')">
            @endif
            <span data-image-fallback @class(['hidden' => $image !== null])><x-admin.icon name="image" /></span>
        </div>
    </td>
    <td class="min-w-0 px-4 py-4">
        <a href="{{ route('admin.products.edit', $product) }}" class="block truncate text-sm font-extrabold text-slate-950 hover:text-indigo-700" title="{{ $name }}">{{ $name }}</a>
        <div class="mt-1 flex min-w-0 items-center gap-2 text-xs text-slate-500">
            <code class="max-w-[12rem] truncate rounded bg-slate-100 px-1.5 py-0.5 font-bold text-slate-600" title="{{ $product->sku }}">{{ $product->sku }}</code>
            @if($variantCount > 0)
                <span class="shrink-0">· {{ trans_choice('admin.products.variant_count', $variantCount, ['count' => $variantCount]) }}</span>
            @endif
            @if($product->is_featured)
                <span class="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 font-bold text-amber-700">{{ __('admin.products.featured') }}</span>
            @endif
        </div>
    </td>
    <td class="px-4 py-4">
        <span class="block truncate text-sm font-semibold text-slate-600" title="{{ $categoryService->name($product->category) }}">{{ $categoryService->name($product->category) }}</span>
    </td>
    <td class="px-4 py-4">
        @if($product->sale_price !== null && (float)$product->sale_price < (float)$product->price)
            <p class="truncate text-sm font-extrabold text-rose-600">{{ $defaultCurrency ? $currencyService->format((float) $product->sale_price, $defaultCurrency) : $product->sale_price }}</p>
            <p class="truncate text-xs font-semibold text-slate-400 line-through">{{ $defaultCurrency ? $currencyService->format((float) $product->price, $defaultCurrency) : $product->price }}</p>
        @else
            <p class="truncate text-sm font-extrabold text-slate-800">{{ $defaultCurrency ? $currencyService->format((float) $product->price, $defaultCurrency) : $product->price }}</p>
        @endif
    </td>
    <td class="px-4 py-4">
        <p class="text-sm font-extrabold text-slate-800">{{ $availableStock }}</p>
        <span @class(['mt-1 inline-flex text-[11px] font-bold', 'text-emerald-700' => $stockStatus === 'in_stock', 'text-amber-700' => $stockStatus === 'low_stock', 'text-rose-700' => $stockStatus === 'out_of_stock'])>{{ __('admin.inventory.'.$stockStatus) }}</span>
    </td>
    <td class="px-4 py-4">
        <span @class(['inline-flex rounded-full px-2.5 py-1 text-[11px] font-extrabold', 'bg-emerald-50 text-emerald-700' => $product->status, 'bg-slate-100 text-slate-600' => ! $product->status])>{{ $product->status ? __('admin.common.active') : __('admin.common.inactive') }}</span>
    </td>
    <td class="px-2 py-4 text-center text-xs font-bold tabular-nums text-slate-500">{{ $sortPosition }}</td>
    <td class="px-4 py-4">
        <time datetime="{{ $product->updated_at?->toIso8601String() }}" class="block truncate text-xs font-semibold text-slate-500">{{ $product->updated_at?->format('Y-m-d') }}</time>
        <span class="text-[11px] text-slate-400">{{ $product->updated_at?->format('H:i') }}</span>
    </td>
    <td class="px-4 py-4">
        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white hover:bg-indigo-700">{{ __('admin.common.edit') }}</a>
            <details class="relative">
                <summary class="cursor-pointer list-none rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">{{ __('admin.products.more') }}</summary>
                <div class="absolute right-0 top-full z-30 mt-1 w-44 rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl">
                    <a href="{{ route('admin.inventory.index', ['keyword' => $product->sku]) }}" class="block rounded-lg px-3 py-2 text-left text-xs font-bold text-slate-700 hover:bg-slate-50">{{ __('admin.products.view_inventory') }}</a>
                    <div class="my-1 border-t border-slate-100"></div>
                    <button type="button" data-async-delete data-delete-url="{{ route('admin.products.destroy', $product) }}" data-delete-target="{{ $deleteTarget }}" data-delete-title="{{ __('admin.products.delete_title') }}" data-delete-message="{{ __('admin.products.delete_message', ['name' => $name]) }}" data-delete-warning="{{ __('admin.products.delete_warning') }}" class="w-full rounded-lg px-3 py-2 text-left text-xs font-bold text-rose-700 hover:bg-rose-50">{{ __('admin.common.delete') }}</button>
                </div>
            </details>
        </div>
    </td>
</tr>
