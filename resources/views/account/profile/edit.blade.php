@extends('layouts.account')

@section('title', __('account.profile').' - '.$siteName)
@section('account-title', __('account.profile'))

@section('account-content')
<section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
    <h2 class="text-lg font-extrabold text-slate-950">{{ __('account.profile_information') }}</h2>
    <p class="mt-1 text-sm text-slate-500">{{ __('account.profile_help') }}</p>

    <form method="POST" action="{{ route('account.profile.update') }}" class="mt-6 grid gap-5 sm:grid-cols-2">
        @csrf
        @method('PATCH')
        <div class="sm:col-span-2">
            <label class="text-sm font-bold text-slate-700">{{ __('account.name') }}</label>
            <input name="name" value="{{ old('name', $user->name) }}" required class="mt-2 w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('name')" class="mt-2"/>
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700">{{ __('account.email') }}</label>
            <input name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-2 w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('email')" class="mt-2"/>
        </div>
        <div>
            <label class="text-sm font-bold text-slate-700">{{ __('account.phone') }}</label>
            <input name="phone" value="{{ old('phone', $user->phone) }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('phone')" class="mt-2"/>
        </div>
        <div class="sm:col-span-2 flex justify-end">
            <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-extrabold text-white hover:bg-indigo-700">{{ __('account.save') }}</button>
        </div>
    </form>
</section>
@endsection
