@extends('layouts.admin')
@section('title', __('admin.online_payment.title'))
@section('content')
    @if(session('success'))<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-bold text-rose-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.settings.payment.online.update') }}" class="space-y-6">@csrf @method('PATCH')
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4"><div><h2 class="font-extrabold">{{ __('admin.online_payment.general') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('admin.online_payment.general_help') }}</p></div><label class="flex items-center gap-2 text-sm font-bold"><input type="checkbox" name="enabled" value="1" @checked(old('enabled', $method->status === 'active')) class="rounded border-slate-300 text-indigo-600">{{ __('admin.online_payment.enabled') }}</label></div>
            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <label class="text-sm font-bold">{{ __('admin.online_payment.display_name') }}<input name="name" value="{{ old('name', $method->name) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></label>
                <label class="text-sm font-bold">{{ __('admin.online_payment.sort_order') }}<input type="number" name="sort_order" value="{{ old('sort_order', $method->sort_order) }}" min="0" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></label>
                <label class="text-sm font-bold md:col-span-2">{{ __('admin.online_payment.description') }}<textarea name="description" rows="3" class="mt-2 block w-full rounded-xl border-slate-300 text-sm">{{ old('description', $method->description) }}</textarea></label>
                <label class="text-sm font-bold md:col-span-2">{{ __('admin.online_payment.instruction') }}<textarea name="instruction" rows="3" class="mt-2 block w-full rounded-xl border-slate-300 text-sm">{{ old('instruction', $method->instruction) }}</textarea></label>
            </div>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="font-extrabold">{{ __('admin.online_payment.gateway') }}</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <label class="text-sm font-bold">{{ __('admin.online_payment.gateway_code') }}<select name="gateway_code" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"><option value="mock" @selected(old('gateway_code', $method->gateway_code) === 'mock')>Mock Gateway</option></select></label>
                <label class="text-sm font-bold">{{ __('admin.online_payment.environment') }}<select name="environment" class="mt-2 block w-full rounded-xl border-slate-300 text-sm">@foreach(['sandbox','live'] as $env)<option value="{{ $env }}" @selected(old('environment', $method->environment) === $env)>{{ ucfirst($env) }}</option>@endforeach</select></label>
                <label class="text-sm font-bold md:col-span-2">{{ __('admin.online_payment.secret_key') }}<input type="password" name="secret_key" autocomplete="new-password" class="mt-2 block w-full rounded-xl border-slate-300 text-sm" placeholder="{{ filled($method->credentials['secret_key'] ?? null) ? __('admin.online_payment.secret_saved') : __('admin.online_payment.secret_placeholder') }}"><span class="mt-1 block text-xs font-normal text-slate-500">{{ __('admin.online_payment.secret_help') }}</span></label>
                <div class="rounded-xl bg-slate-50 p-4 text-xs leading-6 text-slate-600 md:col-span-2"><strong>{{ __('admin.online_payment.return_url') }}:</strong> {{ route('payment.return', 'mock') }}<br><strong>{{ __('admin.online_payment.webhook_url') }}:</strong> {{ route('payment.webhook', 'mock') }}</div>
            </div>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="font-extrabold">{{ __('admin.online_payment.limits') }}</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2"><label class="text-sm font-bold">{{ __('admin.online_payment.min_amount') }}<input type="number" min="0" step="0.01" name="min_order_amount" value="{{ old('min_order_amount', $method->min_order_amount) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></label><label class="text-sm font-bold">{{ __('admin.online_payment.max_amount') }}<input type="number" min="0" step="0.01" name="max_order_amount" value="{{ old('max_order_amount', $method->max_order_amount) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></label></div>
        </section>
        <div class="flex justify-end"><button class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white">{{ __('admin.common.save') }}</button></div>
    </form>
@endsection
