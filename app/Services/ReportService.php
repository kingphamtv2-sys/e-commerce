<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Currency;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function __construct(
        private readonly CurrencyService $currencyService,
        private readonly ProductService $productService,
        private readonly InventoryService $inventoryService,
    ) {}

    public function baseCurrency(): ?Currency
    {
        return $this->currencyService->getDefault();
    }

    public function formatBase(float|int|string|null $amount): string
    {
        $currency = $this->baseCurrency();

        return $currency
            ? $this->currencyService->format((float) $amount, $currency)
            : number_format((float) $amount, 0);
    }

    public function formatOrderAmount(float|int|string|null $amount, object $row): string
    {
        $decimals = (int) ($row->currency_decimal_places ?? 0);
        $symbol = $row->currency_symbol ?? $row->currency_code ?? '';
        $formatted = number_format((float) $amount, $decimals);

        return ($row->currency_symbol_position ?? 'after') === 'before'
            ? $symbol.$formatted
            : trim($formatted.' '.$symbol);
    }

    public function filterOptions(): array
    {
        return [
            'categories' => Category::query()->with('categoryTranslations')->orderBy('id')->get(),
            'products' => Product::query()->with('productTranslations')->orderBy('id')->get(),
        ];
    }

    public function sales(array $filters): array
    {
        $orders = $this->orderQuery($filters)->where('order_status', '!=', 'cancelled');
        $summary = (clone $orders)->selectRaw(
            'COUNT(*) as total_orders,
             COALESCE(SUM(total_amount * exchange_rate), 0) as gross_revenue,
             COALESCE(SUM(CASE WHEN payment_status = ? THEN total_amount * exchange_rate ELSE 0 END), 0) as paid_revenue,
             COALESCE(SUM(CASE WHEN payment_status != ? THEN total_amount * exchange_rate ELSE 0 END), 0) as unpaid_revenue,
             COALESCE(SUM(discount_amount * exchange_rate), 0) as discount_amount,
             COALESCE(SUM(tax_amount * exchange_rate), 0) as tax_amount,
             COALESCE(SUM(shipping_fee * exchange_rate), 0) as shipping_amount',
            ['paid', 'paid'],
        )->first();
        $summary->average_order_value = $summary->total_orders > 0
            ? (float) $summary->gross_revenue / (int) $summary->total_orders
            : 0;
        $summary->net_revenue = (float) $summary->gross_revenue - (float) $summary->discount_amount;

        $trend = (clone $orders)
            ->selectRaw(
                'DATE(COALESCE(placed_at, created_at)) as report_date,
                 COUNT(*) as orders_count,
                 COALESCE(SUM(total_amount * exchange_rate), 0) as gross_revenue,
                 COALESCE(SUM(CASE WHEN payment_status = ? THEN total_amount * exchange_rate ELSE 0 END), 0) as paid_revenue,
                 COALESCE(SUM(discount_amount * exchange_rate), 0) as discount_amount,
                 COALESCE(SUM(tax_amount * exchange_rate), 0) as tax_amount',
                ['paid'],
            )
            ->groupBy(DB::raw('DATE(COALESCE(placed_at, created_at))'))
            ->orderByDesc('report_date')
            ->paginate(31)
            ->withQueryString();

        return compact('summary', 'trend');
    }

    public function orders(array $filters): array
    {
        $orders = $this->orderQuery($filters);
        $counts = (clone $orders)
            ->select('order_status', DB::raw('COUNT(*) as orders_count'), DB::raw('COALESCE(SUM(total_amount * exchange_rate), 0) as total_amount'))
            ->groupBy('order_status')
            ->get()
            ->keyBy('order_status');
        $total = (clone $orders)->count();
        $cancelled = (int) ($counts->get('cancelled')?->orders_count ?? 0);

        return [
            'summary' => [
                'total' => $total,
                'pending' => (int) ($counts->get('pending')?->orders_count ?? 0),
                'confirmed' => (int) ($counts->get('confirmed')?->orders_count ?? 0),
                'processing' => (int) ($counts->get('processing')?->orders_count ?? 0),
                'completed' => (int) ($counts->get('completed')?->orders_count ?? 0),
                'cancelled' => $cancelled,
                'cancellation_rate' => $total > 0 ? round($cancelled * 100 / $total, 2) : 0,
            ],
            'breakdown' => $counts,
            'orders' => (clone $orders)->latest('placed_at')->latest('id')->paginate(15)->withQueryString(),
        ];
    }

    public function productSales(array $filters): array
    {
        $items = $this->orderItemQuery($filters, true);
        $grouped = (clone $items)
            ->selectRaw(
                'order_items.product_id, order_items.product_variant_id, order_items.product_name,
                 order_items.variant_name, COALESCE(order_items.sku, order_items.product_sku) as snapshot_sku,
                 SUM(order_items.quantity) as quantity_sold,
                 COUNT(DISTINCT order_items.order_id) as orders_count,
                 SUM(order_items.subtotal * orders.exchange_rate) as subtotal_amount,
                 SUM(order_items.tax_amount * orders.exchange_rate) as tax_amount,
                 SUM(order_items.total * orders.exchange_rate) as total_revenue',
            )
            ->groupBy(
                'order_items.product_id',
                'order_items.product_variant_id',
                'order_items.product_name',
                'order_items.variant_name',
                DB::raw('COALESCE(order_items.sku, order_items.product_sku)'),
            )
            ->orderByDesc('quantity_sold');
        $summary = (clone $items)->selectRaw(
            'COALESCE(SUM(order_items.quantity), 0) as quantity_sold,
             COALESCE(SUM(order_items.total * orders.exchange_rate), 0) as product_revenue',
        )->first();
        $summary->unique_products = DB::query()->fromSub(
            (clone $items)->selectRaw(
                'order_items.product_id, order_items.product_variant_id, order_items.product_name,
                 COALESCE(order_items.sku, order_items.product_sku) as snapshot_sku',
            )->groupBy(
                'order_items.product_id',
                'order_items.product_variant_id',
                'order_items.product_name',
                DB::raw('COALESCE(order_items.sku, order_items.product_sku)'),
            ),
            'unique_products',
        )->count();
        $bestSeller = (clone $grouped)->first();

        return [
            'summary' => $summary,
            'bestSeller' => $bestSeller,
            'items' => $grouped->paginate(15)->withQueryString(),
        ];
    }

    public function inventory(array $filters): array
    {
        $query = $this->inventoryQuery($filters);
        $summaryBase = $this->inventoryQuery($filters, false);
        $summary = (clone $summaryBase)->selectRaw(
            'COUNT(*) as total_items,
             COALESCE(SUM(quantity - reserved_quantity), 0) as available_quantity,
             COALESCE(SUM(reserved_quantity), 0) as reserved_quantity,
             SUM(CASE WHEN (quantity - reserved_quantity) <= 0 THEN 1 ELSE 0 END) as out_of_stock,
             SUM(CASE WHEN (quantity - reserved_quantity) > 0 AND (quantity - reserved_quantity) <= low_stock_threshold THEN 1 ELSE 0 END) as low_stock',
        )->first();

        return [
            'summary' => $summary,
            'stocks' => $query->latest('inventory_stocks.updated_at')->paginate(15)->withQueryString(),
        ];
    }

    public function coupons(array $filters): array
    {
        $usages = $this->couponUsageQuery($filters);
        $summary = (clone $usages)->selectRaw(
            'COUNT(coupon_usages.id) as total_usages,
             COUNT(DISTINCT coupon_usages.order_id) as orders_count,
             COALESCE(SUM(coupon_usages.discount_amount * orders.exchange_rate), 0) as total_discount',
        )->first();
        $summary->average_discount = $summary->total_usages > 0
            ? (float) $summary->total_discount / (int) $summary->total_usages
            : 0;
        $grouped = (clone $usages)
            ->leftJoin('coupons', 'coupons.id', '=', 'coupon_usages.coupon_id')
            ->selectRaw(
                'coupon_usages.coupon_code, MAX(coupons.name) as coupon_name,
                 COUNT(coupon_usages.id) as usage_count,
                 COUNT(DISTINCT coupon_usages.order_id) as orders_count,
                 SUM(coupon_usages.discount_amount * orders.exchange_rate) as total_discount,
                 SUM(orders.total_amount * orders.exchange_rate) as total_revenue,
                 MAX(COALESCE(coupon_usages.used_at, coupon_usages.created_at)) as last_used',
            )
            ->groupBy('coupon_usages.coupon_code')
            ->orderByDesc('usage_count');

        return [
            'summary' => $summary,
            'topCoupon' => (clone $grouped)->first(),
            'coupons' => $grouped->paginate(15)->withQueryString(),
        ];
    }

    public function taxes(array $filters): array
    {
        $items = $this->orderItemQuery($filters, true)->where('order_items.tax_amount', '>', 0);
        $summary = (clone $items)->selectRaw(
            'COALESCE(SUM(order_items.tax_amount * orders.exchange_rate), 0) as total_tax,
             COALESCE(SUM(order_items.taxable_amount * orders.exchange_rate), 0) as taxable_amount,
             COUNT(DISTINCT order_items.order_id) as orders_count',
        )->first();
        $summary->tax_classes_count = DB::query()->fromSub(
            (clone $items)
                ->selectRaw("COALESCE(order_items.tax_name, 'Tax') as tax_name, order_items.tax_rate")
                ->groupBy(DB::raw("COALESCE(order_items.tax_name, 'Tax')"), 'order_items.tax_rate'),
            'tax_groups',
        )->count();
        $grouped = (clone $items)
            ->selectRaw(
                "COALESCE(order_items.tax_name, 'Tax') as tax_name, order_items.tax_rate,
                 SUM(order_items.taxable_amount * orders.exchange_rate) as taxable_amount,
                 SUM(order_items.tax_amount * orders.exchange_rate) as tax_amount,
                 COUNT(DISTINCT order_items.order_id) as orders_count,
                 SUM(order_items.quantity) as items_count",
            )
            ->groupBy(DB::raw("COALESCE(order_items.tax_name, 'Tax')"), 'order_items.tax_rate')
            ->orderByDesc('tax_amount');

        return [
            'summary' => $summary,
            'taxes' => $grouped->paginate(15)->withQueryString(),
        ];
    }

    public function payments(array $filters): array
    {
        $payments = $this->paymentQuery($filters);
        $summary = (clone $payments)->selectRaw(
            'COUNT(order_payments.id) as total_payments,
             COALESCE(SUM(CASE WHEN order_payments.payment_status = ? THEN order_payments.amount * orders.exchange_rate ELSE 0 END), 0) as paid_amount,
             COALESCE(SUM(CASE WHEN order_payments.payment_status != ? THEN order_payments.amount * orders.exchange_rate ELSE 0 END), 0) as unpaid_amount,
             SUM(CASE WHEN order_payments.payment_method_code = ? THEN 1 ELSE 0 END) as cod_orders,
             COALESCE(SUM(CASE WHEN order_payments.payment_method_code = ? AND order_payments.payment_status IN (?, ?) THEN order_payments.amount * orders.exchange_rate ELSE 0 END), 0) as cod_unpaid_amount,
             SUM(CASE WHEN order_payments.payment_status = ? THEN 1 ELSE 0 END) as failed_payments',
            ['paid', 'paid', 'cod', 'cod', 'unpaid', 'pending', 'failed'],
        )->first();
        $grouped = (clone $payments)
            ->selectRaw(
                'order_payments.payment_method_code, MAX(order_payments.payment_method_name) as payment_method_name,
                 order_payments.payment_status, COUNT(*) as orders_count,
                 SUM(order_payments.amount * orders.exchange_rate) as amount,
                 MAX(COALESCE(order_payments.paid_at, order_payments.created_at)) as last_payment',
            )
            ->groupBy('order_payments.payment_method_code', 'order_payments.payment_status')
            ->orderBy('order_payments.payment_method_code')
            ->orderBy('order_payments.payment_status');

        return [
            'summary' => $summary,
            'payments' => $grouped->paginate(15)->withQueryString(),
        ];
    }

    public function exportRows(string $report, array $filters): iterable
    {
        return match ($report) {
            'sales' => $this->salesExport($filters),
            'orders' => $this->ordersExport($filters),
            'product-sales' => $this->productSalesExport($filters),
            'inventory' => $this->inventoryExport($filters),
            'coupons' => $this->couponExport($filters),
            'taxes' => $this->taxExport($filters),
            'payments' => $this->paymentExport($filters),
        };
    }

    public function exportHeadings(string $report): array
    {
        return match ($report) {
            'sales' => ['Date', 'Orders', 'Gross Revenue (Base)', 'Paid Revenue (Base)', 'Discount (Base)', 'Tax (Base)'],
            'orders' => ['Order Code', 'Customer', 'Email', 'Phone', 'Order Status', 'Payment Status', 'Payment Method', 'Total', 'Currency', 'Placed At'],
            'product-sales' => ['Product Snapshot', 'Variant Snapshot', 'SKU Snapshot', 'Quantity Sold', 'Orders', 'Subtotal (Base)', 'Tax (Base)', 'Total Revenue (Base)'],
            'inventory' => ['Product', 'Variant', 'SKU', 'Quantity', 'Reserved', 'Available', 'Threshold', 'Stock Status', 'Updated At'],
            'coupons' => ['Coupon Code Snapshot', 'Coupon Name', 'Usage Count', 'Orders', 'Total Discount (Base)', 'Order Revenue (Base)', 'Last Used'],
            'taxes' => ['Tax Name Snapshot', 'Tax Rate', 'Taxable Amount (Base)', 'Tax Amount (Base)', 'Orders', 'Items'],
            'payments' => ['Payment Method', 'Payment Status', 'Orders', 'Amount (Base)', 'Last Payment'],
        };
    }

    private function orderQuery(array $filters): Builder
    {
        return Order::query()
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate(DB::raw('COALESCE(placed_at, created_at)'), '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate(DB::raw('COALESCE(placed_at, created_at)'), '<=', $date))
            ->when($filters['order_status'] ?? null, fn (Builder $query, string $status) => $query->where('order_status', $status))
            ->when($filters['payment_status'] ?? null, fn (Builder $query, string $status) => $query->where('payment_status', $status))
            ->when($filters['payment_method'] ?? null, fn (Builder $query, string $method) => $query->where('payment_method', $method));
    }

    private function orderItemQuery(array $filters, bool $excludeCancelled): QueryBuilder
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->when($excludeCancelled && empty($filters['order_status']), fn (QueryBuilder $query) => $query->where('orders.order_status', '!=', 'cancelled'))
            ->when($filters['date_from'] ?? null, fn (QueryBuilder $query, string $date) => $query->whereDate(DB::raw('COALESCE(orders.placed_at, orders.created_at)'), '>=', $date))
            ->when($filters['date_to'] ?? null, fn (QueryBuilder $query, string $date) => $query->whereDate(DB::raw('COALESCE(orders.placed_at, orders.created_at)'), '<=', $date))
            ->when($filters['order_status'] ?? null, fn (QueryBuilder $query, string $status) => $query->where('orders.order_status', $status))
            ->when($filters['payment_status'] ?? null, fn (QueryBuilder $query, string $status) => $query->where('orders.payment_status', $status))
            ->when($filters['payment_method'] ?? null, fn (QueryBuilder $query, string $method) => $query->where('orders.payment_method', $method))
            ->when($filters['product_id'] ?? null, fn (QueryBuilder $query, int|string $id) => $query->where('order_items.product_id', $id))
            ->when($filters['category_id'] ?? null, fn (QueryBuilder $query, int|string $id) => $query->whereIn('order_items.product_id', Product::query()->where('category_id', $id)->select('id')))
            ->when($filters['sku'] ?? null, fn (QueryBuilder $query, string $sku) => $query->whereRaw("LOWER(COALESCE(order_items.sku, order_items.product_sku, '')) LIKE ?", ['%'.strtolower($sku).'%']));
    }

    private function inventoryQuery(array $filters, bool $withRelations = true): Builder
    {
        return InventoryStock::query()
            ->when($withRelations, fn (Builder $query) => $query->with(['product.productTranslations', 'product.productVariants', 'product.category.categoryTranslations', 'productVariant']))
            ->whereHas('product')
            ->where(function (Builder $query): void {
                $query->whereNotNull('product_variant_id')
                    ->orWhereHas('product', fn (Builder $product) => $product->whereDoesntHave('productVariants'));
            })
            ->when($filters['category_id'] ?? null, fn (Builder $query, int|string $id) => $query->whereHas('product', fn (Builder $product) => $product->where('category_id', $id)))
            ->when($filters['product_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('product_id', $id))
            ->when($filters['stock_status'] ?? null, function (Builder $query, string $status): void {
                match ($status) {
                    'out_of_stock' => $query->whereRaw('(quantity - reserved_quantity) <= 0'),
                    'low_stock' => $query->whereRaw('(quantity - reserved_quantity) > 0')->whereRaw('(quantity - reserved_quantity) <= low_stock_threshold'),
                    'in_stock' => $query->whereRaw('(quantity - reserved_quantity) > low_stock_threshold'),
                };
            })
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('inventory_stocks.updated_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('inventory_stocks.updated_at', '<=', $date));
    }

    private function couponUsageQuery(array $filters): QueryBuilder
    {
        return DB::table('coupon_usages')
            ->join('orders', 'orders.id', '=', 'coupon_usages.order_id')
            ->whereNotNull('coupon_usages.order_id')
            ->when($filters['date_from'] ?? null, fn (QueryBuilder $query, string $date) => $query->whereDate(DB::raw('COALESCE(coupon_usages.used_at, coupon_usages.created_at)'), '>=', $date))
            ->when($filters['date_to'] ?? null, fn (QueryBuilder $query, string $date) => $query->whereDate(DB::raw('COALESCE(coupon_usages.used_at, coupon_usages.created_at)'), '<=', $date))
            ->when($filters['order_status'] ?? null, fn (QueryBuilder $query, string $status) => $query->where('orders.order_status', $status))
            ->when($filters['payment_status'] ?? null, fn (QueryBuilder $query, string $status) => $query->where('orders.payment_status', $status));
    }

    private function paymentQuery(array $filters): QueryBuilder
    {
        return DB::table('order_payments')
            ->join('orders', 'orders.id', '=', 'order_payments.order_id')
            ->when($filters['date_from'] ?? null, fn (QueryBuilder $query, string $date) => $query->whereDate(DB::raw('COALESCE(order_payments.paid_at, order_payments.created_at)'), '>=', $date))
            ->when($filters['date_to'] ?? null, fn (QueryBuilder $query, string $date) => $query->whereDate(DB::raw('COALESCE(order_payments.paid_at, order_payments.created_at)'), '<=', $date))
            ->when($filters['order_status'] ?? null, fn (QueryBuilder $query, string $status) => $query->where('orders.order_status', $status))
            ->when($filters['payment_status'] ?? null, fn (QueryBuilder $query, string $status) => $query->where('order_payments.payment_status', $status))
            ->when($filters['payment_method'] ?? null, fn (QueryBuilder $query, string $method) => $query->where('order_payments.payment_method_code', $method));
    }

    private function salesExport(array $filters): iterable
    {
        return $this->orderQuery($filters)
            ->where('order_status', '!=', 'cancelled')
            ->selectRaw(
                'DATE(COALESCE(placed_at, created_at)) as report_date,
                 COUNT(*) as orders_count,
                 COALESCE(SUM(total_amount * exchange_rate), 0) as gross_revenue,
                 COALESCE(SUM(CASE WHEN payment_status = ? THEN total_amount * exchange_rate ELSE 0 END), 0) as paid_revenue,
                 COALESCE(SUM(discount_amount * exchange_rate), 0) as discount_amount,
                 COALESCE(SUM(tax_amount * exchange_rate), 0) as tax_amount',
                ['paid'],
            )
            ->groupBy(DB::raw('DATE(COALESCE(placed_at, created_at))'))
            ->orderByDesc('report_date')
            ->cursor()
            ->map(fn ($row) => [
                $row->report_date, $row->orders_count, $row->gross_revenue, $row->paid_revenue, $row->discount_amount, $row->tax_amount,
            ]);
    }

    private function ordersExport(array $filters): iterable
    {
        return $this->orderQuery($filters)->latest('placed_at')->cursor()->map(fn (Order $order) => [
            $order->order_code, $order->customer_name, $order->customer_email, $order->customer_phone,
            $order->order_status, $order->payment_status, $order->payment_method,
            $order->total_amount, $order->currency_code, ($order->placed_at ?? $order->created_at)?->toDateTimeString(),
        ]);
    }

    private function productSalesExport(array $filters): iterable
    {
        return $this->orderItemQuery($filters, true)
            ->selectRaw(
                'order_items.product_id, order_items.product_variant_id, order_items.product_name,
                 order_items.variant_name, COALESCE(order_items.sku, order_items.product_sku) as snapshot_sku,
                 SUM(order_items.quantity) as quantity_sold,
                 COUNT(DISTINCT order_items.order_id) as orders_count,
                 SUM(order_items.subtotal * orders.exchange_rate) as subtotal_amount,
                 SUM(order_items.tax_amount * orders.exchange_rate) as tax_amount,
                 SUM(order_items.total * orders.exchange_rate) as total_revenue',
            )
            ->groupBy(
                'order_items.product_id', 'order_items.product_variant_id', 'order_items.product_name',
                'order_items.variant_name', DB::raw('COALESCE(order_items.sku, order_items.product_sku)'),
            )
            ->orderByDesc('quantity_sold')
            ->cursor()
            ->map(fn ($row) => [$row->product_name, $row->variant_name, $row->snapshot_sku, $row->quantity_sold, $row->orders_count, $row->subtotal_amount, $row->tax_amount, $row->total_revenue]);
    }

    private function inventoryExport(array $filters): iterable
    {
        return $this->inventoryQuery($filters)->cursor()->map(fn (InventoryStock $stock) => [
            $this->productService->name($stock->product), $this->inventoryService->variantName($stock),
            $this->inventoryService->sku($stock), $stock->quantity, $stock->reserved_quantity,
            $stock->availableQuantity(), $stock->low_stock_threshold, $stock->stockStatus(), $stock->updated_at?->toDateTimeString(),
        ]);
    }

    private function couponExport(array $filters): iterable
    {
        return $this->couponUsageQuery($filters)
            ->leftJoin('coupons', 'coupons.id', '=', 'coupon_usages.coupon_id')
            ->selectRaw(
                'coupon_usages.coupon_code, MAX(coupons.name) as coupon_name,
                 COUNT(coupon_usages.id) as usage_count, COUNT(DISTINCT coupon_usages.order_id) as orders_count,
                 SUM(coupon_usages.discount_amount * orders.exchange_rate) as total_discount,
                 SUM(orders.total_amount * orders.exchange_rate) as total_revenue,
                 MAX(COALESCE(coupon_usages.used_at, coupon_usages.created_at)) as last_used',
            )
            ->groupBy('coupon_usages.coupon_code')
            ->orderByDesc('usage_count')
            ->cursor()
            ->map(fn ($row) => [$row->coupon_code, $row->coupon_name, $row->usage_count, $row->orders_count, $row->total_discount, $row->total_revenue, $row->last_used]);
    }

    private function taxExport(array $filters): iterable
    {
        return $this->orderItemQuery($filters, true)
            ->where('order_items.tax_amount', '>', 0)
            ->selectRaw(
                "COALESCE(order_items.tax_name, 'Tax') as tax_name, order_items.tax_rate,
                 SUM(order_items.taxable_amount * orders.exchange_rate) as taxable_amount,
                 SUM(order_items.tax_amount * orders.exchange_rate) as tax_amount,
                 COUNT(DISTINCT order_items.order_id) as orders_count, SUM(order_items.quantity) as items_count",
            )
            ->groupBy(DB::raw("COALESCE(order_items.tax_name, 'Tax')"), 'order_items.tax_rate')
            ->orderByDesc('tax_amount')
            ->cursor()
            ->map(fn ($row) => [$row->tax_name, $row->tax_rate, $row->taxable_amount, $row->tax_amount, $row->orders_count, $row->items_count]);
    }

    private function paymentExport(array $filters): iterable
    {
        return $this->paymentQuery($filters)
            ->selectRaw(
                'order_payments.payment_method_code, MAX(order_payments.payment_method_name) as payment_method_name,
                 order_payments.payment_status, COUNT(*) as orders_count,
                 SUM(order_payments.amount * orders.exchange_rate) as amount,
                 MAX(COALESCE(order_payments.paid_at, order_payments.created_at)) as last_payment',
            )
            ->groupBy('order_payments.payment_method_code', 'order_payments.payment_status')
            ->orderBy('order_payments.payment_method_code')
            ->orderBy('order_payments.payment_status')
            ->cursor()
            ->map(fn ($row) => [$row->payment_method_name ?: $row->payment_method_code, $row->payment_status, $row->orders_count, $row->amount, $row->last_payment]);
    }
}
