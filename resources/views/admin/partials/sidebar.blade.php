@php
    $menuItems = [
        ['label' => __('admin.menu.dashboard'), 'href' => route('admin.dashboard'), 'pattern' => 'admin.dashboard', 'icon' => 'home'],
        ['label' => __('admin.menu.settings'), 'href' => route('admin.settings.edit'), 'pattern' => 'admin.settings.*', 'icon' => 'cog'],
        ['label' => __('admin.menu.languages'), 'href' => route('admin.languages.index'), 'pattern' => 'admin.languages.*', 'icon' => 'globe'],
        ['label' => __('admin.menu.currencies'), 'href' => route('admin.currencies.index'), 'pattern' => 'admin.currencies.*', 'icon' => 'banknote'],
        ['label' => __('admin.menu.tax_classes'), 'href' => route('admin.tax-classes.index'), 'pattern' => 'admin.tax-classes.*', 'icon' => 'receipt'],
        ['label' => __('admin.menu.tax_rates'), 'href' => route('admin.tax-rates.index'), 'pattern' => 'admin.tax-rates.*', 'icon' => 'percent'],
        ['label' => __('admin.menu.categories'), 'href' => route('admin.categories.index'), 'pattern' => 'admin.categories.*', 'icon' => 'folder'],
        ['label' => __('admin.menu.products'), 'href' => route('admin.products.index'), 'pattern' => 'admin.products.*', 'icon' => 'cube'],
        ['label' => __('admin.menu.inventory'), 'href' => route('admin.inventory.index'), 'pattern' => 'admin.inventory.*', 'icon' => 'archive'],
        ['label' => __('admin.menu.orders'), 'href' => '#', 'pattern' => 'admin.orders*', 'icon' => 'shopping-bag'],
        ['label' => __('admin.menu.customers'), 'href' => '#', 'pattern' => 'admin.customers*', 'icon' => 'users'],
        ['label' => __('admin.menu.coupons'), 'href' => '#', 'pattern' => 'admin.coupons*', 'icon' => 'ticket'],
        ['label' => __('admin.menu.banners'), 'href' => '#', 'pattern' => 'admin.banners*', 'icon' => 'image'],
        ['label' => __('admin.menu.reports'), 'href' => '#', 'pattern' => 'admin.reports*', 'icon' => 'chart-bar'],
    ];
@endphp

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-slate-950 text-white shadow-2xl transition-transform duration-200 ease-out lg:translate-x-0"
>
    <div class="flex h-20 shrink-0 items-center justify-between border-b border-white/10 px-6">
        <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-indigo-500 text-sm font-bold shadow-lg shadow-indigo-950/40">EC</span>
            <span class="min-w-0">
                <span class="block truncate text-sm font-bold tracking-wide">E-commerce</span>
                <span class="block text-xs text-slate-400">Control center</span>
            </span>
        </a>

        <button type="button" class="rounded-lg p-2 text-slate-400 hover:bg-white/10 hover:text-white lg:hidden" @click="sidebarOpen = false" aria-label="Close sidebar">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M6 6l12 12M18 6 6 18" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-4 py-6" aria-label="Admin navigation">
        <p class="mb-3 px-3 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">{{ __('admin.common.management') }}</p>
        <ul class="space-y-1">
            @foreach ($menuItems as $item)
                @php($isActive = request()->routeIs($item['pattern']))
                <li>
                    <a
                        href="{{ $item['href'] }}"
                        @class([
                            'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                            'bg-indigo-500 text-white shadow-lg shadow-indigo-950/30' => $isActive,
                            'text-slate-300 hover:bg-white/10 hover:text-white' => ! $isActive,
                        ])
                        @if ($isActive) aria-current="page" @endif
                    >
                        <span @class([
                            'grid h-9 w-9 shrink-0 place-items-center rounded-lg transition-colors',
                            'bg-white/20 text-white' => $isActive,
                            'bg-slate-800 text-slate-400 group-hover:bg-slate-700 group-hover:text-white' => ! $isActive,
                        ])>
                            <x-admin.icon :name="$item['icon']" />
                        </span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="border-t border-white/10 p-4">
        <div class="rounded-xl bg-white/5 px-4 py-3">
            <p class="text-xs font-semibold text-slate-200">{{ __('admin.common.system_status') }}</p>
            <p class="mt-1 flex items-center gap-2 text-xs text-slate-400">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                {{ __('admin.common.operational') }}
            </p>
        </div>
    </div>
</aside>
