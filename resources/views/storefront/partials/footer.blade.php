<footer class="mt-20 bg-slate-950 text-slate-300">
    <div class="mx-auto grid max-w-[1440px] gap-10 px-4 py-14 sm:px-6 md:grid-cols-3 lg:px-8">
        <div><div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-2xl bg-indigo-600 text-sm font-extrabold text-white">EC</span><span class="text-lg font-extrabold text-white">{{ $siteName }}</span></div><p class="mt-4 max-w-sm text-sm leading-6 text-slate-400">{{ __('storefront.footer_about') }}</p></div>
        <div><h2 class="font-bold text-white">{{ __('storefront.quick_links') }}</h2><div class="mt-4 grid gap-3 text-sm"><a href="{{ route('products.index') }}" class="hover:text-white">{{ __('storefront.products') }}</a><a href="{{ route('products.index').'#categories' }}" class="hover:text-white">{{ __('storefront.categories') }}</a></div></div>
        <div><h2 class="font-bold text-white">{{ __('storefront.customer_care') }}</h2><p class="mt-4 text-sm leading-6 text-slate-400">{{ __('storefront.support_text') }}</p></div>
    </div>
    <div class="border-t border-white/10 px-4 py-5 text-center text-xs text-slate-500">© {{ date('Y') }} {{ $siteName }}. {{ __('storefront.rights') }}</div>
</footer>
