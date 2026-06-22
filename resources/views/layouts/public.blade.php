<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" data-cart-error="{{ __('storefront.cart_error') }}" data-clear-cart-title="{{ __('storefront.clear_cart') }}" data-clear-cart-label="{{ __('storefront.clear_cart') }}" data-cancel-label="{{ __('storefront.cancel') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteName)</title>
    <meta name="description" content="@yield('meta_description', __('storefront.meta_description'))">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 font-sans text-slate-900 antialiased selection:bg-indigo-200 selection:text-indigo-950">
    <div class="flex min-h-screen flex-col">
        @include('storefront.partials.header')
        <main class="flex-1">@yield('content')</main>
        @include('storefront.partials.footer')
    </div>
</body>
</html>
