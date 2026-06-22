<div data-cart-empty @class(['rounded-[2rem] border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm', 'hidden' => ! ($show ?? false)])>
    <div class="mx-auto grid h-20 w-20 place-items-center rounded-3xl bg-indigo-50 text-indigo-600">
        <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h15l-2 8H8L6 3H3"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
    </div>
    <h2 class="mt-6 text-2xl font-extrabold text-slate-950">{{ __('storefront.cart_empty_title') }}</h2>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">{{ __('storefront.cart_empty_description') }}</p>
    <a href="{{ route('products.index') }}" class="mt-7 inline-flex items-center justify-center rounded-2xl bg-slate-950 px-6 py-3 text-sm font-extrabold text-white hover:bg-indigo-700">{{ __('storefront.continue_shopping') }}</a>
</div>
