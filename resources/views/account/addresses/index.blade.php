@extends('layouts.account')

@section('title', __('account.addresses').' - '.$siteName)
@section('account-title', __('account.addresses'))

@section('account-content')
<div x-data="{ deleting: null, deleteName: '' }">
    <div class="mb-5 flex justify-end">
        <a href="{{ route('account.addresses.create') }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-extrabold text-white">{{ __('account.add_address') }}</a>
    </div>
    <div class="grid gap-5 xl:grid-cols-2">
        @forelse($addresses as $address)
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-extrabold text-slate-950">{{ $address->label ?: __('account.address') }}</h2>
                            @if($address->is_default_shipping)<span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-extrabold text-indigo-700">{{ __('account.default_shipping') }}</span>@endif
                            @if($address->is_default_billing)<span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-extrabold text-emerald-700">{{ __('account.default_billing') }}</span>@endif
                        </div>
                        <p class="mt-3 text-sm font-extrabold text-slate-800">{{ $address->recipient_name }} · {{ $address->phone }}</p>
                        <p class="mt-1 text-sm leading-6 text-slate-500">{{ $address->formatted() }}</p>
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                    <a href="{{ route('account.addresses.edit', $address) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-extrabold text-slate-700">{{ __('account.edit') }}</a>
                    @unless($address->is_default_shipping)
                        <form method="POST" action="{{ route('account.addresses.default', $address) }}">@csrf @method('PATCH')<input type="hidden" name="type" value="shipping"><button class="rounded-xl border border-indigo-200 px-3 py-2 text-xs font-extrabold text-indigo-700">{{ __('account.set_shipping') }}</button></form>
                    @endunless
                    @unless($address->is_default_billing)
                        <form method="POST" action="{{ route('account.addresses.default', $address) }}">@csrf @method('PATCH')<input type="hidden" name="type" value="billing"><button class="rounded-xl border border-emerald-200 px-3 py-2 text-xs font-extrabold text-emerald-700">{{ __('account.set_billing') }}</button></form>
                    @endunless
                    <button type="button" @click="deleting = {{ $address->id }}; deleteName = @js($address->label ?: $address->recipient_name)" class="rounded-xl border border-rose-200 px-3 py-2 text-xs font-extrabold text-rose-700">{{ __('account.delete') }}</button>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center xl:col-span-2">
                <p class="font-extrabold text-slate-950">{{ __('account.no_addresses') }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ __('account.no_addresses_help') }}</p>
                <a href="{{ route('account.addresses.create') }}" class="mt-5 inline-flex rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-bold text-white">{{ __('account.add_address') }}</a>
            </div>
        @endforelse
    </div>

    <div x-cloak x-show="deleting !== null" @keydown.escape.window="deleting = null" class="fixed inset-0 z-[80] grid place-items-center p-4" role="dialog" aria-modal="true">
        <button type="button" @click="deleting = null" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" aria-label="{{ __('account.cancel') }}"></button>
        <section x-show="deleting !== null" x-transition class="relative z-10 w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
            <h2 class="text-xl font-extrabold text-slate-950">{{ __('account.delete_address') }}</h2>
            <p class="mt-3 text-sm leading-6 text-slate-500">{{ __('account.delete_address_confirm') }} <strong x-text="deleteName" class="text-slate-800"></strong></p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="deleting = null" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600">{{ __('account.cancel') }}</button>
                @foreach($addresses as $address)
                    <form x-show="deleting === {{ $address->id }}" method="POST" action="{{ route('account.addresses.destroy', $address) }}">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-extrabold text-white">{{ __('account.delete') }}</button>
                    </form>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection
