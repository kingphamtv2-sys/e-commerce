<header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur">
    <div class="flex h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-3">
            <button type="button" class="rounded-xl border border-slate-200 p-2.5 text-slate-600 hover:bg-slate-100 lg:hidden" @click="sidebarOpen = true" aria-label="Open sidebar">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-slate-900">{{ $systemName }}</p>
                <p class="hidden text-xs text-slate-500 sm:block">{{ __('admin.common.administration_portal') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3 sm:gap-4">
            <div class="hidden text-right sm:block">
                <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-500">{{ auth()->user()->email }} · {{ str(auth()->user()->role)->replace('_', ' ')->title() }}</p>
            </div>

            <div class="grid h-10 w-10 place-items-center rounded-full bg-indigo-100 text-sm font-bold uppercase text-indigo-700">
                {{ str(auth()->user()->name)->substr(0, 2) }}
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 sm:px-4">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M10 17l5-5-5-5M15 12H3M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('admin.common.logout') }}</span>
                </button>
            </form>
        </div>
    </div>
</header>
