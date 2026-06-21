@extends('layouts.admin')

@section('title', __('admin.currencies.title'))

@section('page-actions')
    <a href="{{ route('admin.currencies.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700">
        <span class="text-lg leading-none">+</span> {{ __('admin.currencies.add') }}
    </a>
@endsection

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-slate-200 bg-slate-50/70 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-slate-950">{{ __('admin.currencies.supported') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('admin.currencies.supported_desc') }}</p>
            </div>
            <span class="w-fit rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">{{ __('admin.currencies.count', ['count' => count($currencies)]) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-white">
                    <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">{{ __('admin.currencies.currency') }}</th>
                        <th class="px-4 py-4">{{ __('admin.currencies.rate') }}</th>
                        <th class="px-4 py-4">{{ __('admin.currencies.decimals') }}</th>
                        <th class="px-4 py-4">{{ __('admin.currencies.format') }}</th>
                        <th class="px-4 py-4">{{ __('admin.common.active') }}</th>
                        <th class="px-4 py-4">{{ __('admin.common.default') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($currencies as $currency)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-lg font-black text-slate-700">{{ $currency->symbol }}</span>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ $currency->name }}</p>
                                        <code class="mt-0.5 text-xs font-bold text-slate-500">{{ $currency->code }}</code>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-mono text-sm text-slate-700">{{ rtrim(rtrim($currency->exchange_rate, '0'), '.') }}</td>
                            <td class="px-4 py-4 text-sm font-semibold text-slate-600">{{ $currency->decimal_places }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $currencyService->format(1234.5, $currency) }}</td>
                            <td class="px-4 py-4">
                                <span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-bold', 'bg-emerald-50 text-emerald-700' => $currency->status, 'bg-slate-100 text-slate-500' => ! $currency->status])>
                                    {{ $currency->status ? __('admin.common.active') : __('admin.common.inactive') }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                @if ($currency->is_default)
                                    <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700">{{ __('admin.common.default') }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if (! $currency->is_default)
                                        <form method="POST" action="{{ route('admin.currencies.set-default', $currency) }}">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" @disabled(! $currency->status) class="rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-bold text-indigo-700 transition hover:bg-indigo-50 disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400">{{ __('admin.common.set_default') }}</button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.currencies.edit', $currency) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-slate-50">{{ __('admin.common.edit') }}</a>

                                    @if (! $currency->is_default)
                                        <form method="POST" action="{{ route('admin.currencies.destroy', $currency) }}" onsubmit="return confirm(@js(__('admin.currencies.delete_confirm')))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-700 transition hover:bg-rose-50">{{ __('admin.common.delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-16 text-center text-sm text-slate-500">{{ __('admin.currencies.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
