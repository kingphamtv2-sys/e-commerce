<section x-show="activeTab === 'images'" x-cloak data-product-tab="images" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/70 px-6 py-5">
        <div><h2 class="font-bold text-slate-950">{{ __('admin.product_images.title') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('admin.product_images.description') }}</p></div>
        <div class="flex items-center gap-3"><span data-product-image-count class="text-xs font-bold text-slate-500">{{ $productImages->count() }}</span>
            <x-admin.modal id="upload-product-images" :title="__('admin.product_images.upload')" size="max-w-4xl">
                <x-slot:trigger><button type="button" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white">{{ __('admin.product_images.upload') }}</button></x-slot:trigger>
                <form method="POST" action="{{ route('admin.products.images.store', $product) }}" enctype="multipart/form-data" data-async-create data-append-target="#product-image-gallery" class="grid gap-5 md:grid-cols-2">
                    @csrf
                    <div data-async-errors class="hidden md:col-span-2 rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700"></div>
                    <div class="md:col-span-2"><label for="images" class="text-sm font-semibold text-slate-800">{{ __('admin.product_images.files') }} *</label><input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm"><p class="mt-1 text-xs text-slate-500">{{ __('admin.product_images.file_hint') }}</p></div>
                    <div><label for="image_alt_text" class="text-sm font-semibold text-slate-800">{{ __('admin.product_images.alt_text') }}</label><input id="image_alt_text" name="alt_text" maxlength="255" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></div>
                    <div><label for="image_sort_order" class="text-sm font-semibold text-slate-800">{{ __('admin.product_images.sort_order') }}</label><input id="image_sort_order" name="sort_order" type="number" min="0" placeholder="{{ __('admin.product_images.auto') }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></div>
                    <div class="flex flex-wrap items-center gap-5 md:col-span-2"><label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input name="status" type="checkbox" value="1" checked class="h-5 w-5 rounded border-slate-300 text-indigo-600">{{ __('admin.common.active') }}</label><label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input name="is_main" type="checkbox" value="1" class="h-5 w-5 rounded border-slate-300 text-indigo-600">{{ __('admin.product_images.set_first_main') }}</label><button class="ml-auto rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white">{{ __('admin.product_images.upload') }}</button></div>
                </form>
            </x-admin.modal>
        </div>
    </div>

    <div class="p-6">
        <div id="product-image-gallery" class="grid gap-5 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @if ($productImages->isEmpty())<div data-empty-state class="col-span-full rounded-xl border border-dashed border-slate-300 px-6 py-12 text-center text-sm text-slate-500">{{ __('admin.product_images.empty') }}</div>@endif
            @foreach ($productImages as $image)
                @include('admin.products.partials.image-card', compact('image', 'productImageService'))
            @endforeach
        </div>
    </div>
</section>
