@extends('layouts.account')

@section('title', __('account.password').' - '.$siteName)
@section('account-title', __('account.password'))

@section('account-content')
<section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
    <h2 class="text-lg font-extrabold text-slate-950">{{ __('account.change_password') }}</h2>
    <p class="mt-1 text-sm text-slate-500">{{ __('account.password_help') }}</p>
    <form method="POST" action="{{ route('account.password.update') }}" class="mt-6 max-w-xl space-y-5">
        @csrf
        @method('PATCH')
        <div>
            <label class="text-sm font-bold text-slate-700">{{ __('account.current_password') }}</label>
            <input name="current_password" type="password" autocomplete="current-password" required class="mt-2 w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('current_password')" class="mt-2"/>
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700">{{ __('account.new_password') }}</label>
            <input name="password" type="password" autocomplete="new-password" required class="mt-2 w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('password')" class="mt-2"/>
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700">{{ __('account.confirm_password') }}</label>
            <input name="password_confirmation" type="password" autocomplete="new-password" required class="mt-2 w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-extrabold text-white hover:bg-indigo-700">{{ __('account.update_password') }}</button>
    </form>
</section>
@endsection
