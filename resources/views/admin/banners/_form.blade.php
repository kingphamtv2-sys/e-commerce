@if($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><p class="font-bold">{{ __('admin.banners.review') }}</p><ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@php
    $inputClass = 'mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
    $desktopUrl = $bannerService->imageUrl($banner->image_path);
    $mobileUrl = $bannerService->imageUrl($banner->mobile_image_path);
@endphp
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6" x-data="{
    tab: @js(old('active_language', $defaultLanguage?->code ?? $languages->first()?->code)),
    desktopPreview: @js($desktopUrl),
    mobilePreview: @js($mobileUrl),
    preview(event, target) {
        const file = event.target.files?.[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        if (target === 'desktop') this.desktopPreview = url; else this.mobilePreview = url;
    }
}">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.banners.basic') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('admin.banners.basic_desc') }}</p></div>
        <div class="grid gap-6 p-6 md:grid-cols-2 xl:grid-cols-4">
            <div><label for="position" class="text-sm font-semibold text-slate-800">{{ __('admin.banners.position') }}</label><select id="position" name="position" class="{{ $inputClass }}">@foreach($positions as $position)<option value="{{ $position }}" @selected(old('position', $banner->position) === $position)>{{ __('admin.banners.position_'.$position) }}</option>@endforeach</select>@error('position')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div><label for="sort_order" class="text-sm font-semibold text-slate-800">{{ __('admin.banners.sort_order') }}</label><input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" class="{{ $inputClass }}">@error('sort_order')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div><label for="starts_at" class="text-sm font-semibold text-slate-800">{{ __('admin.banners.starts_at') }}</label><input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $banner->starts_at?->format('Y-m-d\\TH:i')) }}" class="{{ $inputClass }}">@error('starts_at')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div><label for="ends_at" class="text-sm font-semibold text-slate-800">{{ __('admin.banners.ends_at') }}</label><input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', $banner->ends_at?->format('Y-m-d\\TH:i')) }}" class="{{ $inputClass }}">@error('ends_at')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-slate-200 p-4 md:col-span-2 xl:col-span-4"><span><span class="block text-sm font-semibold text-slate-800">{{ __('admin.common.active') }}</span><span class="mt-1 block text-xs text-slate-500">{{ __('admin.banners.active_desc') }}</span></span><span><input type="hidden" name="status" value="0"><input name="status" type="checkbox" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-indigo-600" @checked(old('status', $banner->exists ? $banner->status : true))></span></label>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.banners.images') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('admin.banners.images_desc') }}</p></div>
        <div class="grid gap-6 p-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 p-4">
                <label for="image" class="text-sm font-bold text-slate-800">{{ __('admin.banners.desktop_image') }}</label>
                <div class="mt-3 aspect-[16/6] overflow-hidden rounded-xl bg-slate-100"><template x-if="desktopPreview"><img :src="desktopPreview" alt="" class="h-full w-full object-cover"></template><div x-show="!desktopPreview" class="grid h-full place-items-center text-sm text-slate-400">{{ __('admin.banners.image_preview') }}</div></div>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @change="preview($event, 'desktop')" class="mt-4 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-bold file:text-indigo-700">
                @if($banner->image_path)<label class="mt-3 flex items-center gap-2 text-xs font-semibold text-rose-700"><input type="checkbox" name="remove_image" value="1" class="rounded border-rose-300 text-rose-600">{{ __('admin.banners.remove_image') }}</label>@endif
                @error('image')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="rounded-2xl border border-slate-200 p-4">
                <label for="mobile_image" class="text-sm font-bold text-slate-800">{{ __('admin.banners.mobile_image') }}</label>
                <div class="mx-auto mt-3 aspect-[4/3] max-w-xs overflow-hidden rounded-xl bg-slate-100"><template x-if="mobilePreview"><img :src="mobilePreview" alt="" class="h-full w-full object-cover"></template><div x-show="!mobilePreview" class="grid h-full place-items-center text-sm text-slate-400">{{ __('admin.banners.image_preview') }}</div></div>
                <input id="mobile_image" name="mobile_image" type="file" accept="image/jpeg,image/png,image/webp" @change="preview($event, 'mobile')" class="mt-4 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-bold file:text-indigo-700">
                @if($banner->mobile_image_path)<label class="mt-3 flex items-center gap-2 text-xs font-semibold text-rose-700"><input type="checkbox" name="remove_mobile_image" value="1" class="rounded border-rose-300 text-rose-600">{{ __('admin.banners.remove_mobile_image') }}</label>@endif
                @error('mobile_image')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.banners.link') }}</h2></div>
        <div class="grid gap-6 p-6 md:grid-cols-[minmax(0,1fr)_14rem]">
            <div><label for="link_url" class="text-sm font-semibold text-slate-800">{{ __('admin.banners.link_url') }}</label><input id="link_url" name="link_url" maxlength="500" value="{{ old('link_url', $banner->link_url) }}" placeholder="/products or https://example.com" class="{{ $inputClass }}">@error('link_url')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
            <div><label for="link_target" class="text-sm font-semibold text-slate-800">{{ __('admin.banners.link_target') }}</label><select id="link_target" name="link_target" class="{{ $inputClass }}"><option value="same_tab" @selected(old('link_target', $banner->link_target) === 'same_tab')>{{ __('admin.banners.same_tab') }}</option><option value="new_tab" @selected(old('link_target', $banner->link_target) === 'new_tab')>{{ __('admin.banners.new_tab') }}</option></select></div>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.banners.translations') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('admin.banners.translations_desc') }}</p></div>
        <div class="border-b border-slate-200 px-6 pt-4"><div class="flex gap-2 overflow-x-auto" role="tablist">@foreach($languages as $language)<button type="button" @click="tab = @js($language->code)" :class="tab === @js($language->code) ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500'" class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-bold">{{ $language->native_name }} @if($language->code === $defaultLanguage?->code)<span class="text-rose-500">*</span>@endif</button>@endforeach</div></div>
        @foreach($languages as $language)
            @php($translation = $translations->get($language->code))
            <div x-show="tab === @js($language->code)" x-cloak class="grid gap-6 p-6 md:grid-cols-2">
                <div><label class="text-sm font-semibold text-slate-800">{{ __('admin.banners.banner_title') }}</label><input name="translations[{{ $language->code }}][title]" maxlength="255" value="{{ old("translations.{$language->code}.title", $translation?->title) }}" class="{{ $inputClass }}">@error("translations.{$language->code}.title")<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div><label class="text-sm font-semibold text-slate-800">{{ __('admin.banners.subtitle') }}</label><input name="translations[{{ $language->code }}][subtitle]" maxlength="255" value="{{ old("translations.{$language->code}.subtitle", $translation?->subtitle) }}" class="{{ $inputClass }}"></div>
                <div class="md:col-span-2"><label class="text-sm font-semibold text-slate-800">{{ __('admin.banners.description') }}</label><textarea name="translations[{{ $language->code }}][description]" rows="4" class="{{ $inputClass }}">{{ old("translations.{$language->code}.description", $translation?->description) }}</textarea></div>
                <div><label class="text-sm font-semibold text-slate-800">{{ __('admin.banners.button_text') }}</label><input name="translations[{{ $language->code }}][button_text]" maxlength="100" value="{{ old("translations.{$language->code}.button_text", $translation?->button_text) }}" class="{{ $inputClass }}"></div>
                <div><label class="text-sm font-semibold text-slate-800">{{ __('admin.banners.image_alt') }}</label><input name="translations[{{ $language->code }}][image_alt]" maxlength="255" value="{{ old("translations.{$language->code}.image_alt", $translation?->image_alt) }}" class="{{ $inputClass }}"></div>
            </div>
        @endforeach
    </section>

    <div class="flex flex-wrap justify-end gap-3 rounded-2xl border border-slate-200 bg-white px-6 py-4 shadow-sm"><a href="{{ route('admin.banners.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700">{{ __('admin.common.back') }}</a><button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">{{ $submitLabel }}</button></div>
</form>
