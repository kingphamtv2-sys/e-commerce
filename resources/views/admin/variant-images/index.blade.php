@extends('layouts.admin')
@section('title', __('admin.variant_images.title'))
@section('content')
    @if(session('success'))<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div><p class="text-xs font-bold uppercase tracking-wider text-indigo-600">{{ $productService->name($variant->product) }}</p><h1 class="mt-1 text-xl font-extrabold text-slate-950">{{ $variant->name }}</h1><code class="mt-1 block text-xs text-slate-500">{{ $variant->sku }}</code></div>
        <a href="{{ route('admin.products.edit', $variant->product) }}#variant-combinations" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700">{{ __('admin.variant_images.back') }}</a>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.variant_images.upload_title') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('admin.variant_images.description') }}</p></div>
        <form method="POST" action="{{ route('admin.product-variants.images.store', $variant) }}" enctype="multipart/form-data" data-async-create data-append-target="#variant-image-gallery" class="grid gap-5 border-b border-slate-200 p-6 md:grid-cols-2 xl:grid-cols-5">
            @csrf
            <div data-async-errors class="hidden md:col-span-2 xl:col-span-5 rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700"></div>
            <div class="md:col-span-2"><label class="text-sm font-semibold text-slate-800">{{ __('admin.variant_images.files') }} *</label><input name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-violet-50 file:px-3 file:py-2 file:font-semibold file:text-violet-700"><p class="mt-1 text-xs text-slate-500">{{ __('admin.variant_images.file_hint') }}</p></div>
            <div><label class="text-sm font-semibold text-slate-800">{{ __('admin.variant_images.alt_text') }}</label><input name="alt_text" maxlength="255" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></div>
            <div><label class="text-sm font-semibold text-slate-800">{{ __('admin.variant_images.sort_order') }}</label><input name="sort_order" type="number" min="0" placeholder="{{ __('admin.variant_images.auto') }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></div>
            <div class="flex flex-col justify-end gap-3"><label class="flex items-center gap-2 text-sm font-semibold"><input type="hidden" name="status" value="0"><input name="status" type="checkbox" value="1" checked class="rounded border-slate-300 text-indigo-600">{{ __('admin.common.active') }}</label><label class="flex items-center gap-2 text-sm font-semibold"><input type="hidden" name="is_main" value="0"><input name="is_main" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600">{{ __('admin.variant_images.set_first_main') }}</label><button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white">{{ __('admin.variant_images.upload') }}</button></div>
        </form>

        <div class="p-6"><div id="variant-image-gallery" class="grid gap-5 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">@if($images->isEmpty())<div data-empty-state class="col-span-full rounded-xl border border-dashed border-slate-300 px-6 py-12 text-center text-sm text-slate-500">{{ __('admin.variant_images.empty') }}</div>@endif @foreach($images as $image) @include('admin.variant-images._card', compact('image', 'service')) @endforeach</div></div>
    </section>
@endsection
