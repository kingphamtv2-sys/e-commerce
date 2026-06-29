@props(['banners', 'service'])
@if($banners->isNotEmpty())
    <section {{ $attributes->merge(['class' => 'mx-auto max-w-screen-2xl px-4 py-5 sm:px-6 lg:px-8']) }} aria-label="{{ __('storefront.promotions') }}">
        <div class="grid gap-4 {{ $banners->count() > 1 ? 'lg:grid-cols-2' : '' }}">
            @foreach($banners as $banner)
                @php
                    $translation = $banner->displayTranslation;
                    $desktop = $service->imageUrl($banner->image_path);
                    $mobile = $service->imageUrl($banner->mobile_image_path ?: $banner->image_path);
                    $target = $banner->link_target === 'new_tab' ? '_blank' : '_self';
                @endphp
                <article class="group relative min-h-48 overflow-hidden rounded-[1.75rem] bg-slate-900 shadow-sm sm:min-h-64">
                    @if($desktop)
                        <picture class="absolute inset-0">
                            <source media="(max-width: 639px)" srcset="{{ $mobile }}">
                            <img src="{{ $desktop }}" alt="{{ $translation?->image_alt ?: $translation?->title ?: '' }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]">
                        </picture>
                        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-slate-950/45 to-transparent"></div>
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-700 via-violet-700 to-slate-950"></div>
                    @endif
                    <div class="relative z-10 flex min-h-48 max-w-2xl flex-col justify-center p-6 text-white sm:min-h-64 sm:p-10">
                        @if($translation?->subtitle)<p class="text-xs font-extrabold uppercase tracking-[0.2em] text-indigo-200">{{ $translation->subtitle }}</p>@endif
                        @if($translation?->title)<h2 class="mt-2 text-2xl font-black tracking-tight sm:text-4xl">{{ $translation->title }}</h2>@endif
                        @if($translation?->description)<p class="mt-3 line-clamp-3 max-w-xl text-sm leading-6 text-slate-200 sm:text-base">{{ $translation->description }}</p>@endif
                        @if($banner->link_url && $translation?->button_text)
                            <a href="{{ $banner->link_url }}" target="{{ $target }}" @if($target === '_blank') rel="noopener noreferrer" @endif class="mt-5 w-fit rounded-xl bg-white px-5 py-2.5 text-sm font-extrabold text-slate-950 transition hover:bg-indigo-50">{{ $translation->button_text }}</a>
                        @elseif($banner->link_url)
                            <a href="{{ $banner->link_url }}" target="{{ $target }}" @if($target === '_blank') rel="noopener noreferrer" @endif class="absolute inset-0" aria-label="{{ $translation?->title ?: __('storefront.view_promotion') }}"></a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif
