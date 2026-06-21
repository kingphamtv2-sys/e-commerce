@extends('layouts.admin')
@section('title', __('admin.categories.title'))
@section('page-actions')
    <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">+ {{ __('admin.categories.add') }}</a>
@endsection
@section('content')
    @if (session('success')) <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">{{ $errors->first() }}</div> @endif

    <form method="GET" class="mb-6 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-4">
        <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="{{ __('admin.categories.search') }}" class="rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        <select name="status" class="rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">{{ __('admin.categories.all_statuses') }}</option><option value="1" @selected(($filters['status'] ?? '') === '1')>{{ __('admin.common.active') }}</option><option value="0" @selected(($filters['status'] ?? '') === '0')>{{ __('admin.common.inactive') }}</option></select>
        <select name="parent_id" class="rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">{{ __('admin.categories.all_parents') }}</option>@foreach ($parentCategories as $parent)<option value="{{ $parent->id }}" @selected((int) ($filters['parent_id'] ?? 0) === $parent->id)>{{ $categoryService->name($parent) }}</option>@endforeach</select>
        <div class="flex gap-2"><button class="flex-1 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white">{{ __('admin.categories.filter') }}</button><a href="{{ route('admin.categories.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-600">{{ __('admin.categories.reset') }}</a></div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200">
            <thead><tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500"><th class="px-4 py-4">ID</th><th class="px-4 py-4">{{ __('admin.categories.image') }}</th><th class="px-4 py-4">{{ __('admin.categories.name') }}</th><th class="px-4 py-4">{{ __('admin.categories.parent') }}</th><th class="px-4 py-4">{{ __('admin.common.active') }}</th><th class="px-4 py-4">{{ __('admin.categories.sort_order') }}</th><th class="px-4 py-4">{{ __('admin.categories.created_at') }}</th><th class="px-6 py-4 text-right">{{ __('admin.common.actions') }}</th></tr></thead>
            <tbody class="divide-y divide-slate-100">@forelse ($categories as $category)<tr class="hover:bg-slate-50">
                <td class="px-4 py-4 text-sm font-semibold text-slate-500">#{{ $category->id }}</td>
                <td class="px-4 py-4">@if ($category->image)<img src="{{ asset($category->image) }}" alt="" class="h-11 w-11 rounded-lg object-cover">@else<span class="grid h-11 w-11 place-items-center rounded-lg bg-slate-100 text-xs text-slate-400">—</span>@endif</td>
                <td class="px-4 py-4"><p class="font-bold text-slate-900">{{ $categoryService->name($category) }}</p><code class="text-xs text-slate-500">{{ $categoryService->translation($category)?->slug }}</code></td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ $categoryService->name($category->parent) }}</td>
                <td class="px-4 py-4"><span @class(['rounded-full px-2.5 py-1 text-xs font-bold', 'bg-emerald-50 text-emerald-700' => $category->status, 'bg-slate-100 text-slate-500' => ! $category->status])>{{ $category->status ? __('admin.common.active') : __('admin.common.inactive') }}</span></td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ $category->sort_order }}</td><td class="whitespace-nowrap px-4 py-4 text-sm text-slate-500">{{ $category->created_at->format('Y-m-d') }}</td>
                <td class="px-6 py-4"><div class="flex justify-end gap-2"><a href="{{ route('admin.categories.edit', $category) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700">{{ __('admin.common.edit') }}</a><form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm(@js(__('admin.categories.delete_confirm')))" >@csrf @method('DELETE')<button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-700">{{ __('admin.common.delete') }}</button></form></div></td>
            </tr>@empty<tr><td colspan="8" class="px-6 py-16 text-center text-sm text-slate-500">{{ __('admin.categories.empty') }}</td></tr>@endforelse</tbody>
        </table></div>
        @if ($categories->hasPages())<div class="border-t border-slate-200 px-6 py-4">{{ $categories->links() }}</div>@endif
    </div>
@endsection
