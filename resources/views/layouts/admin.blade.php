<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php($systemName = app(\App\Services\SystemSettingService::class)->get('site_name', config('app.name')))

        <title>@yield('title', 'Admin') - {{ $systemName }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen">
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                class="fixed inset-0 z-40 bg-slate-950/60 lg:hidden"
                aria-hidden="true"
                @click="sidebarOpen = false"
                @keydown.escape.window="sidebarOpen = false"
            ></div>

            @include('admin.partials.sidebar')

            <div class="min-h-screen lg:pl-72">
                @include('admin.partials.header')

                <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    <div class="mx-auto max-w-7xl">
                        @include('admin.partials.breadcrumb')

                        <div class="mb-7 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">{{ __('admin.common.admin_workspace') }}</p>
                                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">@yield('title', 'Admin')</h1>
                            </div>
                            @hasSection('page-actions')
                                <div class="flex items-center gap-3">@yield('page-actions')</div>
                            @endif
                        </div>

                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
