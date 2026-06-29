@extends('layouts.admin')

@section('title', __('admin.shipping.zones.title'))

@section('page-actions')
    <a href="{{ route('admin.shipping.methods.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700">{{ __('admin.shipping.methods.title') }}</a>
    <a href="{{ route('admin.shipping.zones.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700"><span class="text-lg leading-none">+</span>{{ __('admin.shipping.zones.add') }}</a>
@endsection

@section('content')
<div x-data="{ openDelete: false, deleteUrl: '', deleteName: '' }">
    @if (session('success'))<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif

    <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[minmax(0,1fr)_180px_auto]">
        <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="{{ __('admin.shipping.search') }}" class="rounded-xl border-slate-300 text-sm font-semibold">
        <select name="status" class="rounded-xl border-slate-300 text-sm font-semibold">
            <option value="">{{ __('admin.shipping.all_statuses') }}</option>
            <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('admin.common.active') }}</option>
            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>{{ __('admin.common.inactive') }}</option>
        </select>
        <div class="flex gap-2"><button class="rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-bold text-white">{{ __('admin.common.filter') }}</button><a href="{{ route('admin.shipping.zones.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700">{{ __('admin.common.reset') }}</a></div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.shipping.zones.list') }}</h2></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead><tr class="text-left text-xs font-bold uppercase text-slate-500"><th class="px-6 py-4">{{ __('admin.shipping.zones.name') }}</th><th class="px-4 py-4">{{ __('admin.shipping.zones.countries') }}</th><th class="px-4 py-4">{{ __('admin.shipping.zones.cities') }}</th><th class="px-4 py-4">{{ __('admin.shipping.methods.count') }}</th><th class="px-4 py-4">{{ __('admin.shipping.status') }}</th><th class="px-4 py-4">{{ __('admin.shipping.sort_order') }}</th><th class="px-6 py-4 text-right">{{ __('admin.common.actions') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($zones as $zone)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4"><p class="font-bold text-slate-950">{{ $zone->name }}</p><p class="mt-1 text-xs font-semibold text-slate-500">{{ $zone->code ?: '—' }}</p></td>
                            <td class="px-4 py-4 text-sm text-slate-600">{{ $zone->countries ? implode(', ', $zone->countries) : __('admin.shipping.all') }}</td>
                            <td class="px-4 py-4 text-sm text-slate-600">{{ $zone->cities ? implode(', ', $zone->cities) : __('admin.shipping.all') }}</td>
                            <td class="px-4 py-4 text-sm font-bold text-slate-700">{{ $zone->methods_count }}</td>
                            <td class="px-4 py-4"><span @class(['rounded-full px-2.5 py-1 text-xs font-bold', 'bg-emerald-50 text-emerald-700' => $zone->status === 'active', 'bg-slate-100 text-slate-500' => $zone->status !== 'active'])>{{ $zone->status === 'active' ? __('admin.common.active') : __('admin.common.inactive') }}</span></td>
                            <td class="px-4 py-4 text-sm">{{ $zone->sort_order }}</td>
                            <td class="px-6 py-4"><div class="flex justify-end gap-2"><a href="{{ route('admin.shipping.zones.edit', $zone) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold">{{ __('admin.common.edit') }}</a><button type="button" @click="openDelete = true; deleteUrl = @js(route('admin.shipping.zones.destroy', $zone)); deleteName = @js($zone->name)" class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-700">{{ __('admin.common.delete') }}</button></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-16 text-center text-sm text-slate-500">{{ __('admin.shipping.zones.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-6 py-4">{{ $zones->links() }}</div>
    </div>

    <div x-show="openDelete" x-transition.opacity class="fixed inset-0 z-[90] grid place-items-center bg-slate-950/60 p-4 backdrop-blur-sm" style="display: none;">
        <div @click.outside="openDelete = false" class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
            <h2 class="text-xl font-extrabold text-slate-950">{{ __('admin.shipping.zones.delete_title') }} <span x-text="deleteName"></span>?</h2>
            <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">{{ __('admin.shipping.zones.delete_warning') }}</p>
            <div class="mt-6 flex justify-end gap-3"><button type="button" @click="openDelete = false" class="rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-extrabold">{{ __('admin.common.cancel') }}</button><form method="POST" x-bind:action="deleteUrl">@csrf @method('DELETE')<button class="rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-extrabold text-white">{{ __('admin.common.delete') }}</button></form></div>
        </div>
    </div>
</div>
@endsection
