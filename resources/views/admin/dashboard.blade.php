@extends('layouts.admin')

@section('title', __('admin.dashboard.title'))

@section('page-actions')
    <form method="GET" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
        <label for="dashboard-range" class="sr-only">{{ __('admin.dashboard.date_range') }}</label>
        <select id="dashboard-range" name="range" class="rounded-lg border-0 py-2 pl-3 pr-9 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500">
            @foreach(['today', 'last7', 'last30', 'this_month'] as $option)
                <option value="{{ $option }}" @selected($range === $option)>{{ __('admin.dashboard.range_'.$option) }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-bold text-white">{{ __('admin.dashboard.apply') }}</button>
    </form>
@endsection

@section('content')
    @php
        $dateQuery = ['date_from' => $rangeStart->toDateString(), 'date_to' => $rangeEnd->toDateString()];
        $productService = app(\App\Services\ProductService::class);
        $maxOrderStatus = max(1, array_sum($statusCounts));
        $maxPaymentStatus = max(1, array_sum($paymentCounts));
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">{{ __('admin.dashboard.showing_range', ['from' => $rangeStart->format('Y-m-d'), 'to' => $rangeEnd->format('Y-m-d')]) }}</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.orders.index') }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">{{ __('admin.dashboard.manage_orders') }}</a>
            <a href="{{ route('admin.products.create') }}" class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100">{{ __('admin.dashboard.add_product') }}</a>
        </div>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="{{ __('admin.dashboard.kpi_overview') }}">
        <x-admin.dashboard.kpi-card :label="__('admin.dashboard.total_revenue')" :value="$kpis['revenue']" :hint="__('admin.dashboard.revenue_hint')" :href="route('admin.orders.index', $dateQuery)" tone="emerald" icon="banknote" />
        <x-admin.dashboard.kpi-card :label="__('admin.dashboard.total_orders')" :value="$kpis['orders']" :hint="__('admin.dashboard.orders_hint')" :href="route('admin.orders.index', $dateQuery)" tone="indigo" icon="shopping-bag" />
        <x-admin.dashboard.kpi-card :label="__('admin.dashboard.pending_orders')" :value="$kpis['pending_orders']" :hint="__('admin.dashboard.pending_hint')" :href="route('admin.orders.index', ['order_status' => 'pending'])" tone="amber" icon="receipt" />
        <x-admin.dashboard.kpi-card :label="__('admin.dashboard.unpaid_cod')" :value="$kpis['unpaid_cod_orders']" :hint="__('admin.dashboard.cod_hint')" :href="route('admin.orders.index', ['payment_status' => 'unpaid', 'payment_method' => 'cod'])" tone="rose" icon="banknote" />
        <x-admin.dashboard.kpi-card :label="__('admin.dashboard.total_products')" :value="$kpis['products']" :hint="__('admin.dashboard.products_hint')" :href="route('admin.products.index')" tone="violet" icon="cube" />
        <x-admin.dashboard.kpi-card :label="__('admin.dashboard.low_stock')" :value="$kpis['low_stock']" :hint="__('admin.dashboard.low_stock_hint')" :href="route('admin.inventory.index', ['stock_status' => 'low_stock'])" tone="sky" icon="archive" />
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(20rem,0.75fr)]">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6">
                <div><h2 class="font-extrabold text-slate-950">{{ __('admin.dashboard.recent_orders') }}</h2><p class="mt-1 text-xs text-slate-500">{{ __('admin.dashboard.recent_orders_hint') }}</p></div>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-indigo-700">{{ __('admin.dashboard.view_all') }}</a>
            </div>
            @if($recentOrders->isEmpty())
                <div class="px-6 py-14 text-center"><p class="font-bold text-slate-700">{{ __('admin.dashboard.no_orders') }}</p><p class="mt-1 text-sm text-slate-500">{{ __('admin.dashboard.no_orders_hint') }}</p></div>
            @else
                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50/70"><tr class="text-left text-[11px] font-extrabold uppercase tracking-wide text-slate-500"><th class="px-5 py-3">{{ __('admin.orders.order') }}</th><th class="px-4 py-3">{{ __('admin.orders.customer') }}</th><th class="px-4 py-3">{{ __('admin.orders.total') }}</th><th class="px-4 py-3">{{ __('admin.orders.order_status') }}</th><th class="px-5 py-3 text-right">{{ __('admin.common.actions') }}</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">@foreach($recentOrders as $order)<tr class="hover:bg-slate-50"><td class="px-5 py-4"><a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-extrabold text-indigo-700">{{ $order->order_code }}</a><p class="mt-1 text-[11px] text-slate-400">{{ ($order->placed_at ?? $order->created_at)?->format('Y-m-d H:i') }}</p></td><td class="max-w-[12rem] px-4 py-4"><p class="truncate text-sm font-bold text-slate-800">{{ $order->customer_name }}</p><x-admin.order-status :status="$order->payment_status" type="payment" /></td><td class="whitespace-nowrap px-4 py-4 text-sm font-extrabold text-slate-800">{{ $dashboardService->formatOrderMoney($order) }}</td><td class="px-4 py-4"><x-admin.order-status :status="$order->order_status" /></td><td class="px-5 py-4 text-right"><a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-bold text-indigo-700">{{ __('admin.orders.view') }}</a></td></tr>@endforeach</tbody>
                    </table>
                </div>
                <div class="divide-y divide-slate-100 md:hidden">@foreach($recentOrders as $order)<a href="{{ route('admin.orders.show', $order) }}" class="block p-5 hover:bg-slate-50"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="font-extrabold text-indigo-700">{{ $order->order_code }}</p><p class="mt-1 truncate text-sm font-semibold text-slate-700">{{ $order->customer_name }}</p></div><p class="shrink-0 text-sm font-extrabold">{{ $dashboardService->formatOrderMoney($order) }}</p></div><div class="mt-3 flex flex-wrap items-center gap-2"><x-admin.order-status :status="$order->order_status" /><x-admin.order-status :status="$order->payment_status" type="payment" /><span class="ml-auto text-xs text-slate-400">{{ ($order->placed_at ?? $order->created_at)?->format('m-d H:i') }}</span></div></a>@endforeach</div>
            @endif
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-center justify-between"><div><h2 class="font-extrabold text-slate-950">{{ __('admin.dashboard.action_required') }}</h2><p class="mt-1 text-xs text-slate-500">{{ __('admin.dashboard.action_hint') }}</p></div><span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-extrabold text-rose-700">{{ $alerts->count() }}</span></div>
            @if($alerts->isEmpty())
                <div class="mt-6 rounded-2xl bg-emerald-50 p-5 text-center"><p class="font-extrabold text-emerald-800">{{ __('admin.dashboard.all_clear') }}</p><p class="mt-1 text-sm text-emerald-700">{{ __('admin.dashboard.all_clear_hint') }}</p></div>
            @else
                <div class="mt-5 space-y-3">@foreach($alerts as $alert)<a href="{{ $alert['href'] }}" class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 px-4 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/40"><div class="min-w-0"><p class="truncate text-sm font-bold text-slate-800">{{ __('admin.dashboard.alert_'.$alert['type'], ['count' => $alert['count']]) }}</p><p class="mt-0.5 text-xs text-slate-400">{{ __('admin.dashboard.review_now') }}</p></div><span class="grid h-8 min-w-8 place-items-center rounded-full bg-rose-50 px-2 text-xs font-black text-rose-700">{{ $alert['count'] }}</span></a>@endforeach</div>
            @endif
        </article>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-extrabold text-slate-950">{{ __('admin.dashboard.order_status_summary') }}</h2>
            <div class="mt-5 space-y-4">@foreach($statusCounts as $status => $count)<a href="{{ route('admin.orders.index', ['order_status' => $status]) }}" class="block"><div class="flex items-center justify-between text-sm"><span class="font-semibold text-slate-600">{{ __('admin.orders.status_'.$status) }}</span><strong>{{ $count }}</strong></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-indigo-500" style="width: {{ ($count / $maxOrderStatus) * 100 }}%"></div></div></a>@endforeach</div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-extrabold text-slate-950">{{ __('admin.dashboard.payment_status_summary') }}</h2>
            <div class="mt-5 space-y-4">@foreach($paymentCounts as $status => $count)<a href="{{ route('admin.orders.index', ['payment_status' => $status]) }}" class="block"><div class="flex items-center justify-between text-sm"><span class="font-semibold text-slate-600">{{ __('admin.orders.payment_'.$status) }}</span><strong>{{ $count }}</strong></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-500" style="width: {{ ($count / $maxPaymentStatus) * 100 }}%"></div></div></a>@endforeach</div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:col-span-2 xl:col-span-1">
            <div class="flex items-center justify-between"><h2 class="font-extrabold text-slate-950">{{ __('admin.dashboard.low_stock_items') }}</h2><a href="{{ route('admin.inventory.index', ['stock_status' => 'low_stock']) }}" class="text-xs font-bold text-indigo-700">{{ __('admin.dashboard.view_all') }}</a></div>
            @if($lowStockItems->isEmpty())
                <div class="mt-6 rounded-xl bg-emerald-50 p-5 text-center text-sm font-bold text-emerald-700">{{ __('admin.dashboard.no_low_stock') }}</div>
            @else
                <div class="mt-4 divide-y divide-slate-100">@foreach($lowStockItems as $stock)<a href="{{ route('admin.inventory.show', $stock) }}" class="flex items-center justify-between gap-3 py-3"><div class="min-w-0"><p class="truncate text-sm font-bold text-slate-800">{{ $productService->name($stock->product) }}</p><p class="truncate text-xs text-slate-400">{{ $stock->productVariant?->name ?: $stock->product->sku }}</p></div><div class="shrink-0 text-right"><p class="text-sm font-black text-amber-700">{{ $stock->availableQuantity() }}</p><p class="text-[10px] text-slate-400">{{ __('admin.dashboard.threshold', ['count' => $stock->low_stock_threshold]) }}</p></div></a>@endforeach</div>
            @endif
        </article>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6"><h2 class="font-extrabold text-slate-950">{{ __('admin.dashboard.top_selling') }}</h2><p class="mt-1 text-xs text-slate-500">{{ __('admin.dashboard.top_selling_hint') }}</p></div>
        @if($topProducts->isEmpty())
            <div class="px-6 py-14 text-center"><p class="font-bold text-slate-700">{{ __('admin.dashboard.no_top_products') }}</p><p class="mt-1 text-sm text-slate-500">{{ __('admin.dashboard.no_top_products_hint') }}</p></div>
        @else
            <div class="grid divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-5">@foreach($topProducts as $item)<div class="p-5"><div class="flex items-start justify-between gap-3"><span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-indigo-50 text-xs font-black text-indigo-700">#{{ $loop->iteration }}</span><span class="text-xs font-bold text-slate-400">{{ __('admin.dashboard.sold', ['count' => $item->quantity_sold]) }}</span></div><p class="mt-4 line-clamp-2 min-h-10 text-sm font-extrabold text-slate-900">{{ $item->product_name }}</p><p class="mt-1 truncate text-xs text-slate-400">{{ $item->snapshot_sku ?: '—' }}</p><p class="mt-3 text-sm font-black text-emerald-700">{{ $item->formatted_revenue }}</p>@if($item->product_url)<a href="{{ $item->product_url }}" class="mt-3 inline-block text-xs font-bold text-indigo-700">{{ __('admin.dashboard.edit_product') }} →</a>@endif</div>@endforeach</div>
        @endif
    </section>
@endsection
