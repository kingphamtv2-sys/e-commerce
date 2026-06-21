<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
        <h2 class="font-bold text-slate-950">{{ __('admin.product_images.title') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('admin.product_images.description') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.products.images.store', $product) }}" enctype="multipart/form-data" class="grid gap-5 border-b border-slate-200 p-6 md:grid-cols-2 xl:grid-cols-5">
        @csrf
        <div class="md:col-span-2 xl:col-span-2">
            <label for="images" class="text-sm font-semibold text-slate-800">{{ __('admin.product_images.files') }} *</label>
            <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-semibold file:text-indigo-700">
            <p class="mt-1 text-xs text-slate-500">{{ __('admin.product_images.file_hint') }}</p>
        </div>
        <div><label for="image_alt_text" class="text-sm font-semibold text-slate-800">{{ __('admin.product_images.alt_text') }}</label><input id="image_alt_text" name="alt_text" maxlength="255" value="{{ old('alt_text') }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></div>
        <div><label for="image_sort_order" class="text-sm font-semibold text-slate-800">{{ __('admin.product_images.sort_order') }}</label><input id="image_sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order') }}" placeholder="{{ __('admin.product_images.auto') }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></div>
        <div class="flex flex-col justify-end gap-3">
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input name="status" type="checkbox" value="1" checked class="h-5 w-5 rounded border-slate-300 text-indigo-600">{{ __('admin.common.active') }}</label>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input name="is_main" type="checkbox" value="1" class="h-5 w-5 rounded border-slate-300 text-indigo-600">{{ __('admin.product_images.set_first_main') }}</label>
            <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">{{ __('admin.product_images.upload') }}</button>
        </div>
    </form>

    <div class="p-6">
        @if ($productImages->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 px-6 py-12 text-center text-sm text-slate-500">{{ __('admin.product_images.empty') }}</div>
        @else
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                @foreach ($productImages as $image)
                    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <div class="relative grid aspect-[4/3] place-items-center bg-slate-100">
                            @if ($productImageService->exists($image))
                                <img src="{{ $productImageService->url($image) }}" alt="{{ $image->alt_text ?? '' }}" class="h-full w-full object-cover">
                            @else
                                <span class="text-sm font-semibold text-slate-400">{{ __('admin.product_images.missing_file') }}</span>
                            @endif
                            @if ($image->is_main)<span class="absolute left-3 top-3 rounded-full bg-indigo-600 px-3 py-1 text-xs font-bold text-white">{{ __('admin.product_images.main') }}</span>@endif
                            <span @class(['absolute right-3 top-3 rounded-full px-3 py-1 text-xs font-bold', 'bg-emerald-100 text-emerald-700' => $image->status, 'bg-slate-200 text-slate-600' => ! $image->status])>{{ $image->status ? __('admin.common.active') : __('admin.common.inactive') }}</span>
                        </div>
                        <form method="POST" action="{{ route('admin.product-images.update', $image) }}" class="space-y-3 p-4">
                            @csrf @method('PUT')
                            <div><label class="text-xs font-bold text-slate-600">{{ __('admin.product_images.alt_text') }}</label><input name="alt_text" maxlength="255" value="{{ $image->alt_text }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"></div>
                            <div><label class="text-xs font-bold text-slate-600">{{ __('admin.product_images.sort_order') }}</label><input name="sort_order" type="number" min="0" value="{{ $image->sort_order }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"></div>
                            <div class="flex items-center justify-between gap-3"><label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input name="status" type="checkbox" value="1" @checked($image->status) class="h-5 w-5 rounded border-slate-300 text-indigo-600">{{ __('admin.common.active') }}</label><label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input name="is_main" type="checkbox" value="1" @checked($image->is_main) class="h-5 w-5 rounded border-slate-300 text-indigo-600">{{ __('admin.product_images.main') }}</label></div>
                            <button class="w-full rounded-lg border border-indigo-200 px-3 py-2 text-sm font-bold text-indigo-700 hover:bg-indigo-50">{{ __('admin.common.save') }}</button>
                        </form>
                        <div class="grid grid-cols-2 gap-2 border-t border-slate-200 p-4">
                            <form method="POST" action="{{ route('admin.product-images.set-main', $image) }}">@csrf @method('PUT')<button @disabled($image->is_main || ! $image->status) class="w-full rounded-lg border border-indigo-200 px-3 py-2 text-xs font-bold text-indigo-700 disabled:cursor-not-allowed disabled:opacity-40">{{ __('admin.product_images.set_main') }}</button></form>
                            <form method="POST" action="{{ route('admin.product-images.destroy', $image) }}" onsubmit="return confirm(@js(__('admin.product_images.delete_confirm')))" >@csrf @method('DELETE')<button class="w-full rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700">{{ __('admin.common.delete') }}</button></form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
