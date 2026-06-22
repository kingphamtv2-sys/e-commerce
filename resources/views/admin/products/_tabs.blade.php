@php
    $tabs = [
        ['key' => 'general', 'label' => __('admin.product_tabs.general'), 'enabled' => true],
        ['key' => 'translations', 'label' => __('admin.product_tabs.translations'), 'enabled' => true],
        ['key' => 'images', 'label' => __('admin.product_tabs.images'), 'enabled' => $isEdit],
        ['key' => 'options', 'label' => __('admin.product_tabs.options'), 'enabled' => $isEdit],
        ['key' => 'variants', 'label' => __('admin.product_tabs.variants'), 'enabled' => $isEdit],
        ['key' => 'variant-images', 'label' => __('admin.product_tabs.variant_images'), 'enabled' => $isEdit],
        ['key' => 'inventory', 'label' => __('admin.product_tabs.inventory'), 'enabled' => $isEdit],
        ['key' => 'seo', 'label' => __('admin.product_tabs.seo'), 'enabled' => true],
    ];
@endphp

<nav class="sticky top-20 z-30 mb-6 rounded-2xl border border-slate-200 bg-white/95 p-2 shadow-sm backdrop-blur" aria-label="{{ __('admin.product_tabs.navigation') }}">
    <div class="flex gap-1 overflow-x-auto">
        @foreach($tabs as $tab)
            <button type="button" @click="select(@js($tab['key']), @js(! $tab['enabled']))" @disabled(! $tab['enabled'])
                :class="activeTab === @js($tab['key']) ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950'"
                class="relative flex shrink-0 items-center gap-2 rounded-xl px-4 py-3 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-40">
                {{ $tab['label'] }}
                <span x-show="errors[@js($tab['key'])]" x-cloak class="rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] leading-none text-white">!</span>
                <span x-show="dirty[@js($tab['key'])] && !errors[@js($tab['key'])]" x-cloak class="h-2 w-2 rounded-full" :class="activeTab === @js($tab['key']) ? 'bg-amber-300' : 'bg-amber-500'" title="{{ __('admin.product_tabs.unsaved') }}"></span>
            </button>
        @endforeach
    </div>
    @unless($isEdit)<p class="px-3 pb-1 pt-2 text-xs font-semibold text-slate-500">{{ __('admin.product_tabs.save_first') }}</p>@endunless
</nav>
