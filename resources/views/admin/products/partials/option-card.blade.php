<article data-product-option="{{ $option->id }}" class="rounded-2xl border border-slate-200 bg-slate-50/40 p-5">
    <form id="option-update-{{ $option->id }}" method="POST" action="{{ route('admin.product-options.update', $option) }}" data-async-save class="grid gap-3 md:grid-cols-[1.2fr_1.2fr_110px_auto_auto]">
        @csrf @method('PUT')
        <input name="name" value="{{ $option->name }}" maxlength="100" required class="rounded-xl border-slate-300 text-sm">
        <input name="display_name" value="{{ $option->display_name }}" maxlength="100" placeholder="{{ __('admin.product_options.display_name') }}" class="rounded-xl border-slate-300 text-sm">
        <input name="sort_order" value="{{ $option->sort_order }}" type="number" min="0" class="rounded-xl border-slate-300 text-sm">
        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" @checked($option->status) class="h-5 w-5 rounded border-slate-300 text-indigo-600">{{ __('admin.common.active') }}</label>
        <div class="flex flex-wrap items-center gap-2"><span data-save-status class="text-xs font-bold" role="status"></span><button class="rounded-xl border border-indigo-200 bg-white px-4 py-2 text-sm font-bold text-indigo-700">{{ __('admin.common.save') }}</button><details class="relative"><summary class="cursor-pointer list-none rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold text-slate-600">•••</summary><div class="absolute right-0 top-full z-10 mt-1 w-32 rounded-lg border bg-white p-1 shadow-xl"><button type="button" data-async-delete data-delete-url="{{ route('admin.product-options.destroy', $option) }}" data-delete-target="[data-product-option='{{ $option->id }}']" data-delete-type="option" data-option-id="{{ $option->id }}" data-delete-title="{{ __('admin.delete_modal.option_title') }}" data-delete-message="{{ __('admin.delete_modal.option_message', ['name' => $option->label()]) }}" data-delete-warning="{{ __('admin.delete_modal.option_warning') }}" class="w-full rounded-md px-2 py-2 text-left text-xs font-bold text-rose-700 hover:bg-rose-50">{{ __('admin.common.delete') }}</button></div></details></div>
    </form>

    <div class="mt-5 border-t border-slate-200 pt-5">
        <div class="mb-3 flex items-center justify-between gap-3"><div class="flex items-center gap-2"><h3 class="text-sm font-extrabold text-slate-900">{{ __('admin.product_options.values') }}</h3><span data-option-value-count="{{ $option->id }}" class="text-xs font-bold text-slate-400">{{ $option->values->count() }}</span></div>
            <x-admin.modal id="add-option-value-{{ $option->id }}" :title="__('admin.product_options.add_value')">
                <x-slot:trigger><button type="button" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white">+ {{ __('admin.product_options.add_value') }}</button></x-slot:trigger>
                <form method="POST" action="{{ route('admin.product-options.values.store', $option) }}" data-async-create data-append-target="#option-values-{{ $option->id }}" data-option-id="{{ $option->id }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <div data-async-errors class="hidden md:col-span-2 rounded-lg bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700"></div>
                    <div><label class="text-xs font-bold text-slate-600">{{ __('admin.product_options.value') }} *</label><input name="value" maxlength="100" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-600">{{ __('admin.product_options.display_value') }}</label><input name="display_value" maxlength="100" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-600">Color</label><input name="color_code" maxlength="20" placeholder="#000000" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></div>
                    <div><label class="text-xs font-bold text-slate-600">{{ __('admin.product_options.sort_order') }}</label><input name="sort_order" type="number" min="0" value="{{ $option->values->count() }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-600"><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" checked class="rounded border-slate-300 text-indigo-600">{{ __('admin.common.active') }}</label>
                    <div class="flex justify-end"><button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white">{{ __('admin.product_options.add_value') }}</button></div>
                </form>
            </x-admin.modal>
        </div>
        <div id="option-values-{{ $option->id }}" class="space-y-2">@foreach ($option->values as $value) @include('admin.products.partials.option-value-row', ['value' => $value]) @endforeach</div>
    </div>
</article>
