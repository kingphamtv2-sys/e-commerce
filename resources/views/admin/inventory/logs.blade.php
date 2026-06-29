@extends('layouts.admin')
@section('title', __('admin.inventory.history_title'))
@section('page-actions')<a href="{{ route('admin.inventory.adjust', $stock) }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white">{{ __('admin.inventory.adjust') }}</a>@endsection
@section('content')
    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-bold text-slate-950">{{ $productService->name($stock->product) }}</h2><p class="mt-1 text-sm text-slate-500">{{ $inventoryService->variantName($stock) }} · {{ $inventoryService->sku($stock) }}</p></section>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5"><h2 class="font-bold text-slate-950">{{ __('admin.inventory.history') }}</h2></div>@include('admin.inventory._logs')</section>
@endsection
