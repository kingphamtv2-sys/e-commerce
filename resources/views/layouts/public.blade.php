<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" data-cart-error="{{ __('storefront.cart_error') }}" data-clear-cart-title="{{ __('storefront.clear_cart') }}" data-clear-cart-label="{{ __('storefront.clear_cart') }}" data-cancel-label="{{ __('storefront.cancel') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteName)</title>
    <meta name="description" content="@yield('meta_description', __('storefront.meta_description'))">
    <link rel="canonical" href="{{ url()->current() }}">
    @if($favicon = $frontendThemeService->imageUrl($frontendTheme['favicon_path'] ?? null))
        <link rel="icon" href="{{ $favicon }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { {{ $frontendThemeCssVariables }} }
        .theme-link { color: var(--theme-link); }
        .theme-link:hover { color: var(--theme-primary); }
        .theme-button { background: var(--theme-button); color: #fff; }
        .theme-button:hover { opacity: .9; }
        .theme-primary-bg { background: var(--theme-primary); }
        .theme-primary-text { color: var(--theme-primary); }
        {!! $frontendTheme['custom_css'] ?? '' !!}
    </style>
</head>
<body class="min-h-screen font-sans antialiased selection:bg-indigo-200 selection:text-indigo-950" style="background: var(--theme-bg); color: var(--theme-text);">
    <div class="flex min-h-screen flex-col">
        @include('storefront.partials.header')
        <main class="flex-1">@yield('content')</main>
        @include('storefront.partials.footer')
    </div>
</body>
</html>
