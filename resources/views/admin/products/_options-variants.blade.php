@php
    $fieldClass = 'mt-1.5 block w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
    $activeOptions = $productOptions->where('status', true);
    $canCreateVariants = $activeOptions->isNotEmpty();
@endphp

<div>
    <section x-show="activeTab === 'options'" x-cloak data-product-tab="options" id="product-options" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/70 px-6 py-5">
            <div><h2 class="font-bold text-slate-950">{{ __('admin.product_options.title') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('admin.product_options.description') }}</p></div>
            <x-admin.modal id="add-product-option" :title="__('admin.product_options.add')">
                <x-slot:trigger><button type="button" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white">+ {{ __('admin.product_options.add') }}</button></x-slot:trigger>
                <form method="POST" action="{{ route('admin.products.options.store', $product) }}" data-async-create data-append-target="#product-option-list" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <div data-async-errors class="hidden md:col-span-2 rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700"></div>
                    <div><label class="text-xs font-bold text-slate-600">{{ __('admin.product_options.name') }} *</label><input name="name" maxlength="100" required placeholder="Color, Size, Storage…" class="{{ $fieldClass }}"></div>
                    <div><label class="text-xs font-bold text-slate-600">{{ __('admin.product_options.display_name') }}</label><input name="display_name" maxlength="100" class="{{ $fieldClass }}"></div>
                    <div><label class="text-xs font-bold text-slate-600">{{ __('admin.product_options.sort_order') }}</label><input name="sort_order" type="number" min="0" value="{{ $productOptions->count() }}" class="{{ $fieldClass }}"></div>
                    <label class="flex items-center gap-2 self-end pb-3 text-sm font-semibold text-slate-700"><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" checked class="h-5 w-5 rounded border-slate-300 text-indigo-600">{{ __('admin.common.active') }}</label>
                    <div class="flex justify-end md:col-span-2"><button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white">{{ __('admin.product_options.add') }}</button></div>
                </form>
            </x-admin.modal>
        </div>

        <div id="product-option-list" class="space-y-5 p-6">
            @forelse ($productOptions as $option)
                @include('admin.products.partials.option-card', ['option' => $option])
            @empty
                <div data-empty-state class="rounded-2xl border border-dashed border-slate-300 px-6 py-10 text-center"><p class="font-bold text-slate-700">{{ __('admin.product_options.empty') }}</p><p class="mt-1 text-sm text-slate-500">{{ __('admin.product_options.empty_hint') }}</p></div>
            @endforelse
        </div>
    </section>

    <section x-show="activeTab === 'variants'" x-cloak data-product-tab="variants" id="variant-combinations" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50/70 px-6 py-5"><div><h2 class="font-bold text-slate-950">{{ __('admin.variant_combinations.title') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('admin.variant_combinations.description') }}</p></div>
            <x-admin.modal id="create-product-variant" :title="__('admin.variant_combinations.add')" size="max-w-5xl">
                <x-slot:trigger><button data-create-variant-trigger type="button" @disabled(! $canCreateVariants) class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-40">+ {{ __('admin.variant_combinations.add') }}</button></x-slot:trigger>
                <form id="variant-create-form" method="POST" action="{{ route('admin.products.variants.store', $product) }}" data-async-create data-append-target="#variant-list">
                    @csrf
                    <div data-async-errors class="mb-4 hidden rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700"></div>
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"><div id="variant-option-selectors" class="contents">@include('admin.products.partials.variant-selectors', ['activeOptions' => $activeOptions])</div><div><label class="text-xs font-bold text-slate-600">SKU *</label><input name="sku" maxlength="100" required class="{{ $fieldClass }}"></div><div><label class="text-xs font-bold text-slate-600">{{ __('admin.variant_combinations.custom_name') }}</label><input name="name" maxlength="255" placeholder="{{ __('admin.variant_combinations.auto_name') }}" class="{{ $fieldClass }}"></div><div><label class="text-xs font-bold text-slate-600">{{ __('admin.products.price') }}</label><input name="price" type="number" min="0" step="0.01" class="{{ $fieldClass }}"></div><div><label class="text-xs font-bold text-slate-600">{{ __('admin.products.sale_price') }}</label><input name="sale_price" type="number" min="0" step="0.01" class="{{ $fieldClass }}"></div></div>
                    <div class="mt-5 flex items-center justify-between"><label class="flex items-center gap-2 text-sm font-semibold text-slate-700"><input type="hidden" name="status" value="0"><input name="status" type="checkbox" value="1" checked class="h-5 w-5 rounded border-slate-300 text-indigo-600">{{ __('admin.common.active') }}</label><button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white">{{ __('admin.variant_combinations.add') }}</button></div>
                </form>
            </x-admin.modal>
        </div>

        <div id="variant-setup-hint" @class(['border-b border-amber-200 bg-amber-50 px-6 py-4 text-sm font-semibold text-amber-800', 'hidden' => $canCreateVariants])>{{ __('admin.variant_combinations.setup_hint') }}</div>

        <div id="variant-list" class="space-y-4 p-6">
            @include('admin.products.partials.variant-list', ['variants' => $variants, 'activeOptions' => $activeOptions])
        </div>
    </section>
</div>

@once
    @php
        $asyncSaveLabels = [
            'unsaved' => __('admin.common.unsaved'),
            'saving' => __('admin.common.saving'),
            'saved' => __('admin.common.saved'),
            'error' => __('admin.common.save_error'),
        ];
    @endphp
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const labels = {{ Illuminate\Support\Js::from($asyncSaveLabels) }};
                const editors = [];
                const pendingRequests = new WeakMap();

                const setState = (form, state, message = null) => {
                    form.dataset.saveState = state;
                    const status = form.querySelector('[data-save-status]');
                    if (!status) return;

                    status.textContent = state === 'saved' ? '' : (message || labels[state]);
                    status.title = message || '';
                    status.classList.toggle('hidden', state === 'saved');
                    status.classList.remove('text-amber-600', 'text-indigo-600', 'text-emerald-600', 'text-rose-600');
                    status.classList.add({ unsaved: 'text-amber-600', saving: 'text-indigo-600', saved: 'text-emerald-600', error: 'text-rose-600' }[state]);
                };

                const markUnsaved = (form) => {
                    form.dataset.changeVersion = String(Number(form.dataset.changeVersion || 0) + 1);
                    setState(form, 'unsaved');
                };

                const saveForm = async (form) => {
                    if (pendingRequests.has(form)) return pendingRequests.get(form);
                    if (!form.reportValidity()) {
                        setState(form, 'error', labels.error);
                        return false;
                    }

                    const version = form.dataset.changeVersion || '0';
                    setState(form, 'saving');
                    window.adminLoading?.show();
                    form.querySelectorAll('button[type="submit"], button:not([type])').forEach(button => button.disabled = true);

                    const request = fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    }).then(async response => {
                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            const firstError = Object.values(payload.errors || {}).flat()[0] || payload.message || labels.error;
                            setState(form, 'error', firstError);
                            window.adminToast?.(firstError, 'error');
                            const tab = form.closest('[data-product-tab]')?.dataset.productTab;
                            if (tab) window.dispatchEvent(new CustomEvent('admin:tab-error', { detail: { tab } }));
                            return false;
                        }

                        setState(form, form.dataset.changeVersion === version ? 'saved' : 'unsaved');
                        window.adminToast?.(payload.message || labels.saved);
                        const tab = form.closest('[data-product-tab]')?.dataset.productTab;
                        if (tab && form.dataset.changeVersion === version) window.dispatchEvent(new CustomEvent('admin:tab-saved', { detail: { tab } }));
                        window.syncVariantManagement?.(payload);
                        return true;
                    }).catch(() => {
                        setState(form, 'error', labels.error);
                        window.adminToast?.(labels.error, 'error');
                        return false;
                    }).finally(() => {
                        pendingRequests.delete(form);
                        form.querySelectorAll('button[type="submit"], button:not([type])').forEach(button => button.disabled = false);
                        window.adminLoading?.hide();
                    });

                    pendingRequests.set(form, request);
                    return request;
                };

                const bindEditor = form => {
                    if (form.dataset.asyncSaveBound) return;
                    form.dataset.asyncSaveBound = 'true';
                    editors.push(form);
                    form.dataset.saveState = 'saved';
                    form.dataset.changeVersion = '0';
                    form.addEventListener('input', () => markUnsaved(form));
                    form.addEventListener('change', () => markUnsaved(form));
                    form.addEventListener('submit', event => {
                        event.preventDefault();
                        saveForm(form);
                    });
                };

                document.querySelectorAll('form[data-async-save]').forEach(bindEditor);
                document.addEventListener('admin:content-updated', event => event.detail.root.querySelectorAll('form[data-async-save]').forEach(bindEditor));

                const productForm = document.getElementById('product-main-form');
                let allowProductSubmit = false;
                productForm?.addEventListener('submit', async event => {
                    if (allowProductSubmit) return;
                    event.preventDefault();

                    const dirtyEditors = editors.filter(form => form.isConnected && form.dataset.saveState !== 'saved');
                    for (const editor of dirtyEditors) {
                        if (!await saveForm(editor)) {
                            editor.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            return;
                        }
                    }

                    allowProductSubmit = true;
                    productForm.requestSubmit(event.submitter || undefined);
                });
            });
        </script>
    @endpush
@endonce
