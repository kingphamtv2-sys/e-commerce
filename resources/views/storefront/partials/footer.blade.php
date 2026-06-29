@php
    $brandName = $frontendTheme['brand_name'] ?: $siteName;
    $logoUrl = $frontendThemeService->imageUrl($frontendTheme['logo_path'] ?? null);
    $socials = [
        'Facebook' => $frontendTheme['facebook_url'] ?? null,
        'Instagram' => $frontendTheme['instagram_url'] ?? null,
        'YouTube' => $frontendTheme['youtube_url'] ?? null,
        'TikTok' => $frontendTheme['tiktok_url'] ?? null,
    ];
@endphp
<footer class="mt-20 text-slate-300" style="background: var(--theme-secondary);">
    <div class="mx-auto grid max-w-[1440px] gap-10 px-4 py-14 sm:px-6 md:grid-cols-3 lg:px-8">
        <div>
            <div class="flex items-center gap-3">@if($logoUrl)<img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="h-11 max-w-36 object-contain">@else<span class="grid h-11 w-11 place-items-center rounded-2xl text-sm font-extrabold text-white" style="background: var(--theme-primary);">{{ str($brandName)->substr(0, 2)->upper() }}</span>@endif<span class="text-lg font-extrabold text-white">{{ $brandName }}</span></div>
            <p class="mt-4 max-w-sm text-sm leading-6 text-slate-300">{{ $frontendTheme['store_description'] ?: __('storefront.footer_about') }}</p>
            @if($frontendTheme['footer_text'])<p class="mt-3 text-sm leading-6 text-slate-400">{{ $frontendTheme['footer_text'] }}</p>@endif
        </div>
        <div><h2 class="font-bold text-white">{{ __('storefront.quick_links') }}</h2><div class="mt-4 grid gap-3 text-sm"><a href="{{ route('home') }}" class="hover:text-white">{{ __('storefront.home') }}</a><a href="{{ route('products.index') }}" class="hover:text-white">{{ __('storefront.products') }}</a><a href="{{ route('home').'#categories' }}" class="hover:text-white">{{ __('storefront.categories') }}</a></div>@if(collect($socials)->filter()->isNotEmpty())<div class="mt-5 flex flex-wrap gap-3 text-sm">@foreach($socials as $label => $url)@if($url)<a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-white/10 px-3 py-1.5 hover:bg-white/10">{{ $label }}</a>@endif @endforeach</div>@endif</div>
        <div><h2 class="font-bold text-white">{{ __('storefront.customer_care') }}</h2><div class="mt-4 space-y-2 text-sm leading-6 text-slate-300">@if($frontendTheme['contact_email'])<p>{{ $frontendTheme['contact_email'] }}</p>@endif @if($frontendTheme['contact_phone'])<p>{{ $frontendTheme['contact_phone'] }}</p>@endif @if($frontendTheme['address'])<p>{{ $frontendTheme['address'] }}</p>@endif @if(!$frontendTheme['contact_email'] && !$frontendTheme['contact_phone'] && !$frontendTheme['address'])<p>{{ __('storefront.support_text') }}</p>@endif</div></div>
    </div>
    <div class="border-t border-white/10 px-4 py-5 text-center text-xs text-slate-400">{{ $frontendTheme['copyright_text'] ?: ('© '.date('Y').' '.$brandName.'. '.__('storefront.rights')) }}</div>
</footer>
