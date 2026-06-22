<div data-admin-delete-modal class="pointer-events-none fixed inset-0 z-[75] grid place-items-center p-4 opacity-0 transition-opacity duration-200 ease-out" aria-hidden="true">
    <div data-delete-backdrop class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"></div>
    <section data-delete-panel class="relative z-10 w-full max-w-lg translate-y-3 scale-95 rounded-2xl bg-white opacity-0 shadow-2xl transition duration-200 ease-out" role="dialog" aria-modal="true" aria-labelledby="admin-delete-modal-title">
        <div class="border-b border-slate-200 px-6 py-5">
            <div class="flex items-start gap-4">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-rose-100 text-rose-700">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>
                </span>
                <div class="min-w-0">
                    <h2 id="admin-delete-modal-title" data-delete-title class="text-lg font-extrabold text-slate-950"></h2>
                    <p data-delete-message class="mt-1 text-sm font-semibold text-slate-600"></p>
                </div>
            </div>
        </div>

        <div class="space-y-4 px-6 py-5">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                <p data-delete-warning></p>
            </div>
            <div data-delete-errors class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700"></div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
            <button type="button" data-delete-cancel class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100">{{ __('admin.common.cancel') }}</button>
            <button type="button" data-delete-confirm class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60">
                <span data-delete-spinner class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                <span data-delete-button-label>{{ __('admin.common.delete') }}</span>
            </button>
        </div>
    </section>
</div>
