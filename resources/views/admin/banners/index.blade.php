@extends('layouts.admin')
@section('title', __('admin.banners.title'))
@section('page-actions')<a href="{{ route('admin.banners.create') }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white">+ {{ __('admin.banners.add') }}</a>@endsection
@section('content')
    @if(session('success'))<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
    <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-2 xl:grid-cols-5">
        <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="{{ __('admin.banners.search') }}" class="rounded-xl border-slate-300 text-sm">
        <select name="position" class="rounded-xl border-slate-300 text-sm"><option value="">{{ __('admin.banners.all_positions') }}</option>@foreach($positions as $position)<option value="{{ $position }}" @selected(($filters['position'] ?? '') === $position)>{{ __('admin.banners.position_'.$position) }}</option>@endforeach</select>
        <select name="status" class="rounded-xl border-slate-300 text-sm"><option value="">{{ __('admin.banners.all_statuses') }}</option><option value="1" @selected(($filters['status'] ?? '') === '1')>{{ __('admin.common.active') }}</option><option value="0" @selected(($filters['status'] ?? '') === '0')>{{ __('admin.common.inactive') }}</option></select>
        <select name="schedule" class="rounded-xl border-slate-300 text-sm"><option value="">{{ __('admin.banners.all_schedules') }}</option>@foreach(['active_now','scheduled','expired'] as $schedule)<option value="{{ $schedule }}" @selected(($filters['schedule'] ?? '') === $schedule)>{{ __('admin.banners.schedule_'.$schedule) }}</option>@endforeach</select>
        <div class="flex gap-2"><button class="flex-1 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white">{{ __('admin.banners.filter') }}</button><a href="{{ route('admin.banners.index') }}" class="rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-bold text-slate-600">{{ __('admin.banners.reset') }}</a></div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="min-w-[900px] w-full divide-y divide-slate-200">
            <thead class="bg-slate-50/70"><tr class="text-left text-xs font-bold uppercase tracking-wide text-slate-500"><th class="px-5 py-4">{{ __('admin.banners.image') }}</th><th class="px-5 py-4">{{ __('admin.banners.banner') }}</th><th class="px-4 py-4">{{ __('admin.banners.position') }}</th><th class="px-4 py-4">{{ __('admin.banners.status') }}</th><th class="px-4 py-4">{{ __('admin.banners.schedule') }}</th><th class="px-3 py-4 text-center">{{ __('admin.banners.sort_order') }}</th><th class="px-4 py-4">{{ __('admin.banners.updated') }}</th><th class="px-5 py-4 text-right">{{ __('admin.common.actions') }}</th></tr></thead>
            <tbody class="divide-y divide-slate-100">@forelse($banners as $banner)
                @php
                    $translation = $service->translation($banner);
                    $schedule = $service->scheduleStatus($banner);
                @endphp
                <tr data-banner-row="{{ $banner->id }}" class="hover:bg-slate-50">
                <td class="px-5 py-4">@if($banner->image_path)<img src="{{ $service->imageUrl($banner->image_path) }}" alt="" class="h-12 w-20 rounded-lg object-cover">@else<span class="grid h-12 w-20 place-items-center rounded-lg bg-slate-100 text-xs text-slate-400">—</span>@endif</td>
                <td class="max-w-xs px-5 py-4"><p class="truncate font-bold text-slate-900">{{ $translation?->title ?: __('admin.banners.untitled') }}</p><p class="mt-1 truncate text-xs text-slate-500">{{ $banner->link_url ?: __('admin.banners.no_link') }}</p></td>
                <td class="px-4 py-4 text-sm font-semibold text-slate-600">{{ __('admin.banners.position_'.$banner->position) }}</td>
                <td class="px-4 py-4"><span @class(['rounded-full px-2.5 py-1 text-xs font-bold','bg-emerald-50 text-emerald-700'=>$banner->status,'bg-slate-100 text-slate-600'=>!$banner->status])>{{ $banner->status ? __('admin.common.active') : __('admin.common.inactive') }}</span></td>
                <td class="px-4 py-4"><span @class(['rounded-full px-2.5 py-1 text-xs font-bold','bg-emerald-50 text-emerald-700'=>$schedule==='active_now','bg-sky-50 text-sky-700'=>$schedule==='scheduled','bg-rose-50 text-rose-700'=>$schedule==='expired','bg-slate-100 text-slate-600'=>$schedule==='inactive'])>{{ __('admin.banners.schedule_'.$schedule) }}</span><p class="mt-1 text-[11px] text-slate-400">{{ $banner->starts_at?->format('Y-m-d H:i') ?: '—' }} → {{ $banner->ends_at?->format('Y-m-d H:i') ?: '∞' }}</p></td>
                <td class="px-3 py-4 text-center text-sm font-bold text-slate-600">{{ $banner->sort_order }}</td><td class="px-4 py-4 text-xs text-slate-500">{{ $banner->updated_at?->format('Y-m-d H:i') }}</td>
                <td class="px-5 py-4"><div class="flex justify-end gap-2"><a href="{{ route('admin.banners.edit', $banner) }}" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white">{{ __('admin.common.edit') }}</a><details class="relative"><summary class="cursor-pointer list-none rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600">{{ __('admin.banners.more') }}</summary><div class="absolute right-0 top-full z-30 mt-1 w-36 rounded-xl border bg-white p-1 shadow-xl"><button type="button" data-async-delete data-delete-url="{{ route('admin.banners.destroy', $banner) }}" data-delete-target="[data-banner-row='{{ $banner->id }}']" data-delete-title="{{ __('admin.banners.delete_title') }}" data-delete-message="{{ __('admin.banners.delete_message', ['title' => $translation?->title ?: '#'.$banner->id]) }}" data-delete-warning="{{ __('admin.banners.delete_warning') }}" class="w-full rounded-lg px-3 py-2 text-left text-xs font-bold text-rose-700 hover:bg-rose-50">{{ __('admin.common.delete') }}</button></div></details></div></td>
            </tr>@empty<tr><td colspan="8" class="px-6 py-16 text-center"><p class="font-bold text-slate-700">{{ __('admin.banners.empty') }}</p><a href="{{ route('admin.banners.create') }}" class="mt-3 inline-block text-sm font-bold text-indigo-700">{{ __('admin.banners.add') }}</a></td></tr>@endforelse</tbody>
        </table></div>
        @if($banners->hasPages())<div class="border-t border-slate-200 px-6 py-4">{{ $banners->links() }}</div>@endif
    </div>
    @include('admin.products.partials.delete-modal')
@endsection
