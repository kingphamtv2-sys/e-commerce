@extends('layouts.admin')

@section('title', __('admin.languages.title'))

@section('page-actions')
    <a href="{{ route('admin.languages.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700">
        <span class="text-lg leading-none">+</span> {{ __('admin.languages.add') }}
    </a>
@endsection

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-slate-200 bg-slate-50/70 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-slate-950">{{ __('admin.languages.supported') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('admin.languages.supported_desc') }}</p>
            </div>
            <span class="w-fit rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">{{ __('admin.languages.count', ['count' => count($languages)]) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-white">
                    <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">{{ __('admin.languages.language') }}</th>
                        <th class="px-4 py-4">{{ __('admin.languages.code') }}</th>
                        <th class="px-4 py-4">{{ __('admin.common.active') }}</th>
                        <th class="px-4 py-4">{{ __('admin.common.default') }}</th>
                        <th class="px-4 py-4">{{ __('admin.languages.order') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($languages as $language)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-xs font-black uppercase text-slate-600">{{ $language->code }}</span>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ $language->name }}</p>
                                        <p class="mt-0.5 text-sm text-slate-500">{{ $language->native_name ?: '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4"><code class="rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-700">{{ $language->code }}</code></td>
                            <td class="px-4 py-4">
                                <span @class([
                                    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold',
                                    'bg-emerald-50 text-emerald-700' => $language->status,
                                    'bg-slate-100 text-slate-500' => ! $language->status,
                                ])>
                                    <span @class(['h-1.5 w-1.5 rounded-full', 'bg-emerald-500' => $language->status, 'bg-slate-400' => ! $language->status])></span>
                                    {{ $language->status ? __('admin.common.active') : __('admin.common.inactive') }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                @if ($language->is_default)
                                    <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700">{{ __('admin.common.default') }}</span>
                                @else
                                    <span class="text-sm text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm font-semibold text-slate-600">{{ $language->sort_order }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if (! $language->is_default)
                                        <form method="POST" action="{{ route('admin.languages.set-default', $language) }}">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" @disabled(! $language->status) class="rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-bold text-indigo-700 transition hover:bg-indigo-50 disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400" title="{{ $language->status ? 'Set as default' : 'Activate before setting as default' }}">
                                                {{ __('admin.common.set_default') }}
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.languages.edit', $language) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">{{ __('admin.common.edit') }}</a>

                                    @if (! $language->is_default)
                                        <form method="POST" action="{{ route('admin.languages.destroy', $language) }}" onsubmit="return confirm(@js(__('admin.languages.delete_confirm')))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-700 transition hover:bg-rose-50">{{ __('admin.common.delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-16 text-center text-sm text-slate-500">{{ __('admin.languages.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
