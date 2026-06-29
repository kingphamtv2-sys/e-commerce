@php
    $showStatuses = $showStatuses ?? true;
    $showPayment = $showPayment ?? true;
    $showProducts = $showProducts ?? false;
    $showStock = $showStock ?? false;
@endphp

<form method="GET" class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <label class="text-xs font-bold uppercase tracking-wide text-slate-500">
            {{ __('admin.reports.date_from') }}
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm">
        </label>
        <label class="text-xs font-bold uppercase tracking-wide text-slate-500">
            {{ __('admin.reports.date_to') }}
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm">
        </label>
        @if($showStatuses)
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">
                {{ __('admin.reports.order_status') }}
                <select name="order_status" class="mt-1 block w-full rounded-xl border-slate-300 text-sm">
                    <option value="">{{ __('admin.reports.all') }}</option>
                    @foreach(\App\Services\ReportFilterService::ORDER_STATUSES as $status)
                        <option value="{{ $status }}" @selected(($filters['order_status'] ?? '') === $status)>{{ __("admin.orders.status_{$status}") }}</option>
                    @endforeach
                </select>
            </label>
        @endif
        @if($showPayment)
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">
                {{ __('admin.reports.payment_status') }}
                <select name="payment_status" class="mt-1 block w-full rounded-xl border-slate-300 text-sm">
                    <option value="">{{ __('admin.reports.all') }}</option>
                    @foreach(\App\Services\ReportFilterService::PAYMENT_STATUSES as $status)
                        <option value="{{ $status }}" @selected(($filters['payment_status'] ?? '') === $status)>{{ __("admin.orders.payment_{$status}") }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">
                {{ __('admin.reports.payment_method') }}
                <select name="payment_method" class="mt-1 block w-full rounded-xl border-slate-300 text-sm">
                    <option value="">{{ __('admin.reports.all') }}</option>
                    <option value="cod" @selected(($filters['payment_method'] ?? '') === 'cod')>COD</option>
                    <option value="online" @selected(($filters['payment_method'] ?? '') === 'online')>{{ __('admin.menu.online_payment') }}</option>
                </select>
            </label>
        @endif
        @if($showProducts)
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">
                {{ __('admin.reports.product') }}
                <select name="product_id" class="mt-1 block w-full rounded-xl border-slate-300 text-sm">
                    <option value="">{{ __('admin.reports.all') }}</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected((string)($filters['product_id'] ?? '') === (string)$product->id)>{{ app(\App\Services\ProductService::class)->name($product) }} ({{ $product->sku }})</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">
                {{ __('admin.reports.category') }}
                <select name="category_id" class="mt-1 block w-full rounded-xl border-slate-300 text-sm">
                    <option value="">{{ __('admin.reports.all') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string)($filters['category_id'] ?? '') === (string)$category->id)>{{ $category->categoryTranslations->first()?->name ?? "#{$category->id}" }}</option>
                    @endforeach
                </select>
            </label>
            @unless($showStock)
                <label class="text-xs font-bold uppercase tracking-wide text-slate-500">
                    SKU
                    <input name="sku" value="{{ $filters['sku'] ?? '' }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm" placeholder="{{ __('admin.reports.sku_snapshot') }}">
                </label>
            @endunless
        @endif
        @if($showStock)
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">
                {{ __('admin.reports.stock_status') }}
                <select name="stock_status" class="mt-1 block w-full rounded-xl border-slate-300 text-sm">
                    <option value="">{{ __('admin.reports.all') }}</option>
                    @foreach(['in_stock','low_stock','out_of_stock'] as $status)
                        <option value="{{ $status }}" @selected(($filters['stock_status'] ?? '') === $status)>{{ __("admin.reports.{$status}") }}</option>
                    @endforeach
                </select>
            </label>
        @endif
    </div>
    @if($errors->any())
        <p class="mt-3 text-sm font-semibold text-rose-600">{{ $errors->first() }}</p>
    @endif
    <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
        <a href="{{ $resetRoute }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">{{ __('admin.reports.reset') }}</a>
        <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-700">{{ __('admin.reports.apply') }}</button>
        <a href="{{ $exportRoute }}" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-500">{{ __('admin.reports.export_csv') }}</a>
    </div>
</form>
