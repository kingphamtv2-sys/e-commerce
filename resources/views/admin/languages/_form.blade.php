@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
        <p class="font-bold">{{ __('admin.languages.review') }}</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $action }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
        <h2 class="font-bold text-slate-950">{{ __('admin.languages.details') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('admin.languages.details_desc') }}</p>
    </div>

    <div class="grid gap-6 p-6 md:grid-cols-2">
        <div>
            <label for="code" class="text-sm font-semibold text-slate-800">{{ __('admin.languages.language_code') }} <span class="text-rose-500">*</span></label>
            <input id="code" name="code" type="text" maxlength="10" value="{{ old('code', $language->code) }}" class="mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm uppercase shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="fr" required>
            <p class="mt-1 text-xs text-slate-500">ISO-style code such as vi, en, ja or fr.</p>
            @error('code') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="sort_order" class="text-sm font-semibold text-slate-800">{{ __('admin.languages.sort_order') }}</label>
            <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $language->sort_order ?? 0) }}" class="mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('sort_order') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="name" class="text-sm font-semibold text-slate-800">{{ __('admin.languages.english_name') }} <span class="text-rose-500">*</span></label>
            <input id="name" name="name" type="text" maxlength="100" value="{{ old('name', $language->name) }}" class="mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="French" required>
            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="native_name" class="text-sm font-semibold text-slate-800">{{ __('admin.languages.native_name') }}</label>
            <input id="native_name" name="native_name" type="text" maxlength="100" value="{{ old('native_name', $language->native_name) }}" class="mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Français">
            @error('native_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-slate-200 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/30">
            <span>
                <span class="block text-sm font-semibold text-slate-800">{{ __('admin.common.active') }}</span>
                <span class="mt-1 block text-xs leading-5 text-slate-500">{{ __('admin.languages.active_desc') }}</span>
            </span>
            <input name="status" type="checkbox" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('status', $language->exists ? $language->status : true))>
        </label>

        <label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-slate-200 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/30">
            <span>
                <span class="block text-sm font-semibold text-slate-800">{{ __('admin.languages.default_language') }}</span>
                <span class="mt-1 block text-xs leading-5 text-slate-500">{{ __('admin.languages.default_desc') }}</span>
            </span>
            <input name="is_default" type="checkbox" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked(old('is_default', $language->is_default))>
        </label>
    </div>

    <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50/70 px-6 py-4">
        <a href="{{ route('admin.languages.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100">{{ __('admin.common.back') }}</a>
        <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700">{{ $submitLabel }}</button>
    </div>
</form>
