<header x-data="{ mobileOpen: false }" class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">
    <div class="bg-slate-950 px-4 py-2 text-center text-xs font-semibold tracking-wide text-slate-200">
        {{ __('storefront.top_message') }}
    </div>
    <div class="mx-auto flex h-20 max-w-[1440px] items-center gap-4 px-4 sm:px-6 lg:px-8">
        <button type="button" @click="mobileOpen = ! mobileOpen" class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-slate-200 text-slate-700 lg:hidden" aria-label="{{ __('storefront.menu') }}">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <a href="{{ route('products.index') }}" class="flex shrink-0 items-center gap-3">
            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-sm font-extrabold text-white shadow-lg shadow-indigo-200">EC</span>
            <span class="hidden text-lg font-extrabold tracking-tight text-slate-950 sm:block">{{ $siteName }}</span>
        </a>
        <nav class="hidden items-center gap-7 lg:flex">
            <a href="{{ route('products.index') }}" class="text-sm font-semibold text-slate-600 transition hover:text-indigo-600">{{ __('storefront.home') }}</a>
            <a href="{{ route('products.index') }}" class="text-sm font-bold text-indigo-600">{{ __('storefront.products') }}</a>
            <a href="{{ route('products.index').'#categories' }}" class="text-sm font-semibold text-slate-600 transition hover:text-indigo-600">{{ __('storefront.categories') }}</a>
        </nav>
        <form action="{{ route('products.index') }}" method="GET" class="relative ml-auto hidden w-full max-w-md md:block">
            <input type="hidden" name="language" value="{{ $currentLanguage->code }}"><input type="hidden" name="currency" value="{{ $currentCurrency->code }}">
            <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="{{ __('storefront.search_placeholder') }}" class="h-12 w-full rounded-2xl border-slate-200 bg-slate-100/80 pl-12 pr-4 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500">
            <svg class="absolute left-4 top-3.5 h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        </form>
        <div class="hidden items-center gap-2 sm:flex">
            <form method="GET"><input type="hidden" name="currency" value="{{ $currentCurrency->code }}">@foreach(request()->except(['language','page','currency']) as $key => $value)@if(is_scalar($value) && $value !== '')<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach<select name="language" onchange="this.form.submit()" class="h-11 rounded-xl border-slate-200 bg-white py-0 pl-3 pr-8 text-xs font-bold"><option value="{{ $currentLanguage->code }}">{{ strtoupper($currentLanguage->code) }}</option>@foreach($languages->where('code','!=',$currentLanguage->code) as $language)<option value="{{ $language->code }}">{{ strtoupper($language->code) }}</option>@endforeach</select></form>
            <form method="GET"><input type="hidden" name="language" value="{{ $currentLanguage->code }}">@foreach(request()->except(['currency','page','language']) as $key => $value)@if(is_scalar($value) && $value !== '')<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach<select name="currency" onchange="this.form.submit()" class="h-11 rounded-xl border-slate-200 bg-white py-0 pl-3 pr-8 text-xs font-bold">@foreach($currencies as $currency)<option value="{{ $currency->code }}" @selected($currency->code === $currentCurrency->code)>{{ $currency->code }}</option>@endforeach</select></form>
        </div>
        <a href="{{ auth()->check() ? route('account.index') : route('login') }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-slate-200 text-slate-600 transition hover:border-indigo-200 hover:text-indigo-600" aria-label="{{ __('storefront.account') }}"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></a>
        <a href="{{ route('cart.index') }}" class="relative grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-slate-950 text-white transition hover:bg-indigo-700" aria-label="{{ __('storefront.cart') }}" title="{{ __('storefront.cart') }}"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 7h15l-2 8H8L6 3H3"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg><span data-cart-count @class(['absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-indigo-500 px-1 text-[10px] font-bold', 'hidden' => ($cartCount ?? 0) < 1])>{{ $cartCount ?? 0 }}</span></a>
    </div>
    <div x-show="mobileOpen" x-cloak class="border-t border-slate-200 bg-white px-4 py-4 lg:hidden">
        <form action="{{ route('products.index') }}" method="GET" class="relative mb-4"><input type="hidden" name="language" value="{{ $currentLanguage->code }}"><input type="hidden" name="currency" value="{{ $currentCurrency->code }}"><input name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="{{ __('storefront.search_placeholder') }}" class="h-12 w-full rounded-xl border-slate-200 pl-11 text-sm"><svg class="absolute left-3.5 top-3.5 h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></form>
        <nav class="grid gap-1"><a href="{{ route('products.index') }}" class="rounded-xl bg-indigo-50 px-4 py-3 text-sm font-bold text-indigo-700">{{ __('storefront.products') }}</a><a href="{{ route('products.index').'#categories' }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-slate-700">{{ __('storefront.categories') }}</a></nav>
    </div>
</header>
