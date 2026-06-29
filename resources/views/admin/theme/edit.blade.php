@extends('layouts.admin')

@section('title', __('admin.theme.title'))

@section('content')
@php
    $input = 'mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
    $colorInput = 'mt-2 h-11 w-full rounded-xl border-slate-300 p-1 shadow-sm';
    $checkbox = 'h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500';
@endphp

<div x-data="{ resetOpen: false }" class="space-y-6">
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <p class="font-bold">{{ __('admin.theme.review') }}</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.theme.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PATCH')

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.theme.brand') }}</h2></div>
            <div class="grid gap-6 p-6 md:grid-cols-3">
                <div>
                    <label class="text-sm font-semibold text-slate-800">{{ __('admin.theme.brand_name') }}</label>
                    <input name="brand_name" value="{{ old('brand_name', $themeValues['brand_name']) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-800">{{ __('admin.theme.logo') }}</label>
                    @if($url = $themeService->imageUrl($themeValues['logo_path']))
                        <div class="mt-2 flex h-20 items-center rounded-xl border border-slate-200 bg-slate-50 px-4"><img src="{{ $url }}" alt="" class="max-h-14 max-w-full"></div>
                        <label class="mt-2 flex items-center gap-2 text-xs font-bold text-slate-600"><input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-rose-600"> {{ __('admin.theme.remove_logo') }}</label>
                    @endif
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="{{ $input }}">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-800">{{ __('admin.theme.favicon') }}</label>
                    @if($url = $themeService->imageUrl($themeValues['favicon_path']))
                        <div class="mt-2 grid h-20 w-20 place-items-center rounded-xl border border-slate-200 bg-slate-50"><img src="{{ $url }}" alt="" class="max-h-10 max-w-10"></div>
                        <label class="mt-2 flex items-center gap-2 text-xs font-bold text-slate-600"><input type="checkbox" name="remove_favicon" value="1" class="rounded border-slate-300 text-rose-600"> {{ __('admin.theme.remove_favicon') }}</label>
                    @endif
                    <input type="file" name="favicon" accept="image/png,image/jpeg,image/webp,image/x-icon" class="{{ $input }}">
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.theme.colors') }}</h2></div>
            <div class="grid gap-5 p-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach(['primary_color','secondary_color','text_color','button_color','link_color','background_color'] as $key)
                    <div>
                        <label class="text-sm font-semibold text-slate-800">{{ __('admin.theme.'.$key) }}</label>
                        <input type="color" name="{{ $key }}" value="{{ old($key, $themeValues[$key]) }}" class="{{ $colorInput }}">
                    </div>
                @endforeach
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.theme.hero') }}</h2></div>
            <div class="grid gap-6 p-6 md:grid-cols-2">
                <label class="flex items-start justify-between gap-4 rounded-xl border border-slate-200 p-4 md:col-span-2"><span><span class="block text-sm font-bold">{{ __('admin.theme.hero_enabled') }}</span></span><input type="checkbox" name="hero_enabled" value="1" @checked(old('hero_enabled', $themeValues['hero_enabled'])) class="{{ $checkbox }}"></label>
                <div>
                    <label class="text-sm font-semibold text-slate-800">{{ __('admin.theme.hero_title') }}</label>
                    <input name="hero_title" value="{{ old('hero_title', $themeValues['hero_title']) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-800">{{ __('admin.theme.hero_button_text') }}</label>
                    <input name="hero_button_text" value="{{ old('hero_button_text', $themeValues['hero_button_text']) }}" class="{{ $input }}">
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-800">{{ __('admin.theme.hero_subtitle') }}</label>
                    <textarea name="hero_subtitle" rows="3" class="{{ $input }}">{{ old('hero_subtitle', $themeValues['hero_subtitle']) }}</textarea>
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-800">{{ __('admin.theme.hero_button_url') }}</label>
                    <input name="hero_button_url" value="{{ old('hero_button_url', $themeValues['hero_button_url']) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="text-sm font-semibold text-slate-800">{{ __('admin.theme.hero_image') }}</label>
                    @if($url = $themeService->imageUrl($themeValues['hero_image_path']))
                        <img src="{{ $url }}" alt="" class="mt-2 h-28 w-full rounded-xl object-cover">
                        <label class="mt-2 flex items-center gap-2 text-xs font-bold text-slate-600"><input type="checkbox" name="remove_hero_image" value="1" class="rounded border-slate-300 text-rose-600"> {{ __('admin.theme.remove_hero_image') }}</label>
                    @endif
                    <input type="file" name="hero_image" accept="image/png,image/jpeg,image/webp" class="{{ $input }}">
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.theme.home_sections') }}</h2></div>
            <div class="grid gap-4 p-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach(['show_featured_categories','show_featured_products','show_new_arrivals','show_best_sellers','show_promotion_banner','show_newsletter'] as $key)
                    <label class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 p-4 text-sm font-bold text-slate-800">{{ __('admin.theme.'.$key) }}<input type="checkbox" name="{{ $key }}" value="1" @checked(old($key, $themeValues[$key])) class="{{ $checkbox }}"></label>
                @endforeach
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.theme.footer') }}</h2></div>
            <div class="grid gap-6 p-6 md:grid-cols-2">
                @foreach(['footer_text','copyright_text','store_description','contact_email','contact_phone','address'] as $key)
                    <div @class(['md:col-span-2' => in_array($key, ['store_description','address'], true)])>
                        <label class="text-sm font-semibold text-slate-800">{{ __('admin.theme.'.$key) }}</label>
                        @if(in_array($key, ['store_description','address'], true))
                            <textarea name="{{ $key }}" rows="3" class="{{ $input }}">{{ old($key, $themeValues[$key]) }}</textarea>
                        @else
                            <input name="{{ $key }}" value="{{ old($key, $themeValues[$key]) }}" class="{{ $input }}">
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.theme.social') }}</h2></div>
            <div class="grid gap-6 p-6 md:grid-cols-2">
                @foreach(['facebook_url','instagram_url','youtube_url','tiktok_url'] as $key)
                    <div><label class="text-sm font-semibold text-slate-800">{{ __('admin.theme.'.$key) }}</label><input name="{{ $key }}" value="{{ old($key, $themeValues[$key]) }}" class="{{ $input }}"></div>
                @endforeach
            </div>
        </section>

        @if(in_array(auth()->user()->role, ['super_admin', 'admin'], true))
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.theme.custom_css') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('admin.theme.custom_css_help') }}</p></div>
                <div class="p-6"><textarea name="custom_css" rows="10" class="{{ $input }} font-mono">{{ old('custom_css', $themeValues['custom_css']) }}</textarea></div>
            </section>
        @endif

        <div class="flex flex-col justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-6 py-4 shadow-sm sm:flex-row sm:items-center">
            <button type="button" @click="resetOpen = true" class="rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-bold text-rose-700 hover:bg-rose-50">{{ __('admin.theme.reset') }}</button>
            <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">{{ __('admin.theme.save') }}</button>
        </div>
    </form>

    <div x-show="resetOpen" x-transition.opacity class="fixed inset-0 z-[90] grid place-items-center bg-slate-950/60 p-4 backdrop-blur-sm" style="display:none">
        <div @click.outside="resetOpen = false" class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
            <h2 class="text-xl font-extrabold text-slate-950">{{ __('admin.theme.reset_title') }}</h2>
            <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">{{ __('admin.theme.reset_warning') }}</p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="resetOpen = false" class="rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-extrabold">{{ __('admin.common.cancel') }}</button>
                <form method="POST" action="{{ route('admin.theme.reset') }}">@csrf @method('DELETE')<button class="rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-extrabold text-white">{{ __('admin.theme.reset') }}</button></form>
            </div>
        </div>
    </div>
</div>
@endsection
