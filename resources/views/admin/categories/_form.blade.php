@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
        <p class="font-bold">{{ __('admin.categories.review') }}</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    @php($inputClass = 'mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500')

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
            <h2 class="font-bold text-slate-950">{{ __('admin.categories.general') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('admin.categories.general_desc') }}</p>
        </div>
        <div class="grid gap-6 p-6 md:grid-cols-2">
            <div>
                <label for="parent_id" class="text-sm font-semibold text-slate-800">{{ __('admin.categories.parent') }}</label>
                <select id="parent_id" name="parent_id" class="{{ $inputClass }}">
                    <option value="">{{ __('admin.categories.no_parent') }}</option>
                    @foreach ($parentCategories as $parent)
                        <option value="{{ $parent->id }}" @selected((int) old('parent_id', $category->parent_id) === $parent->id)>{{ $categoryService->name($parent) }}</option>
                    @endforeach
                </select>
                @error('parent_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="image" class="text-sm font-semibold text-slate-800">{{ __('admin.categories.image') }}</label>
                <input id="image" name="image" maxlength="500" value="{{ old('image', $category->image) }}" placeholder="categories/fashion.jpg" class="{{ $inputClass }}">
                @error('image') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="sort_order" class="text-sm font-semibold text-slate-800">{{ __('admin.categories.sort_order') }}</label>
                <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="{{ $inputClass }}">
                @error('sort_order') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-slate-200 p-4">
                <span><span class="block text-sm font-semibold text-slate-800">{{ __('admin.common.active') }}</span><span class="mt-1 block text-xs text-slate-500">{{ __('admin.categories.active_desc') }}</span></span>
                <input name="status" type="checkbox" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-indigo-600" @checked(old('status', $category->exists ? $category->status : true))>
            </label>
        </div>
    </section>

    <section x-data="{ tab: @js(old('active_language', $defaultLanguage?->code ?? ($languages[0]->code ?? 'vi'))) }" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
            <h2 class="font-bold text-slate-950">{{ __('admin.categories.translations') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('admin.categories.translations_desc') }}</p>
        </div>
        <div class="border-b border-slate-200 px-6 pt-4">
            <div class="flex gap-2 overflow-x-auto" role="tablist">
                @foreach ($languages as $language)
                    <button type="button" @click="tab = @js($language->code)" :class="tab === @js($language->code) ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:text-slate-800'" class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-bold">
                        {{ $language->native_name }} @if ($language->code === $defaultLanguage?->code)<span class="text-rose-500">*</span>@endif
                    </button>
                @endforeach
            </div>
        </div>
        @foreach ($languages as $language)
            @php($translation = $translations->get($language->code))
            <div x-show="tab === @js($language->code)" x-cloak class="grid gap-6 p-6 md:grid-cols-2">
                <div>
                    <label for="name_{{ $language->code }}" class="text-sm font-semibold text-slate-800">{{ __('admin.categories.name') }} @if ($language->code === $defaultLanguage?->code)*@endif</label>
                    <input id="name_{{ $language->code }}" name="translations[{{ $language->code }}][name]" maxlength="255" value="{{ old("translations.{$language->code}.name", $translation?->name) }}" class="{{ $inputClass }}" @required($language->code === $defaultLanguage?->code)>
                    @error("translations.{$language->code}.name") <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="slug_{{ $language->code }}" class="text-sm font-semibold text-slate-800">{{ __('admin.categories.slug') }}</label>
                    <input id="slug_{{ $language->code }}" name="translations[{{ $language->code }}][slug]" maxlength="255" value="{{ old("translations.{$language->code}.slug", $translation?->slug) }}" class="{{ $inputClass }}" placeholder="{{ __('admin.categories.slug_hint') }}">
                    @error("translations.{$language->code}.slug") <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="description_{{ $language->code }}" class="text-sm font-semibold text-slate-800">{{ __('admin.categories.description') }}</label>
                    <textarea id="description_{{ $language->code }}" name="translations[{{ $language->code }}][description]" rows="4" class="{{ $inputClass }}">{{ old("translations.{$language->code}.description", $translation?->description) }}</textarea>
                </div>
                <div>
                    <label for="meta_title_{{ $language->code }}" class="text-sm font-semibold text-slate-800">{{ __('admin.categories.meta_title') }}</label>
                    <input id="meta_title_{{ $language->code }}" name="translations[{{ $language->code }}][meta_title]" maxlength="255" value="{{ old("translations.{$language->code}.meta_title", $translation?->meta_title) }}" class="{{ $inputClass }}">
                </div>
                <div>
                    <label for="meta_description_{{ $language->code }}" class="text-sm font-semibold text-slate-800">{{ __('admin.categories.meta_description') }}</label>
                    <textarea id="meta_description_{{ $language->code }}" name="translations[{{ $language->code }}][meta_description]" rows="3" class="{{ $inputClass }}">{{ old("translations.{$language->code}.meta_description", $translation?->meta_description) }}</textarea>
                </div>
            </div>
        @endforeach
    </section>

    <div class="flex justify-end gap-3 rounded-2xl border border-slate-200 bg-white px-6 py-4 shadow-sm">
        <a href="{{ route('admin.categories.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">{{ __('admin.common.back') }}</a>
        <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">{{ $submitLabel }}</button>
    </div>
</form>
