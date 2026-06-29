@extends('layouts.public')

@section('content')
<div class="min-h-screen bg-slate-50">
    <section class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <div class="mb-6">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-indigo-600">{{ __('account.eyebrow') }}</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">@yield('account-title')</h1>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
            <aside class="h-max rounded-3xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="mb-3 rounded-2xl bg-slate-950 p-4 text-white">
                    <p class="truncate text-sm font-extrabold">{{ auth()->user()->name }}</p>
                    <p class="mt-1 truncate text-xs text-slate-300">{{ auth()->user()->email }}</p>
                </div>
                <nav class="grid gap-1 text-sm">
                    @foreach([
                        ['account.index', 'dashboard'],
                        ['account.profile.edit', 'profile'],
                        ['account.addresses.index', 'addresses'],
                        ['account.orders.index', 'orders'],
                        ['account.password.edit', 'password'],
                    ] as [$routeName, $label])
                        <a href="{{ route($routeName) }}" @class([
                            'rounded-xl px-4 py-3 font-bold transition',
                            'bg-indigo-50 text-indigo-700' => request()->routeIs($routeName) || ($routeName === 'account.addresses.index' && request()->routeIs('account.addresses.*')) || ($routeName === 'account.orders.index' && request()->routeIs('account.orders.*')),
                            'text-slate-600 hover:bg-slate-50 hover:text-slate-950' => ! (request()->routeIs($routeName) || ($routeName === 'account.addresses.index' && request()->routeIs('account.addresses.*')) || ($routeName === 'account.orders.index' && request()->routeIs('account.orders.*'))),
                        ])>{{ __('account.nav.'.$label) }}</a>
                    @endforeach
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-full rounded-xl px-4 py-3 text-left font-bold text-rose-600 transition hover:bg-rose-50">{{ __('account.nav.logout') }}</button>
                    </form>
                </nav>
            </aside>

            <main class="min-w-0">
                @yield('account-content')
            </main>
        </div>
    </section>
</div>
@endsection
