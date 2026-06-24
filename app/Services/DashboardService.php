<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public const DEFAULT_RANGE = 'last7';

    public const RANGES = ['today', 'last7', 'last30', 'this_month'];

    public function __construct(
        private readonly CurrencyService $currencyService,
        private readonly ProductService $productService,
    ) {}

    public function data(?string $requestedRange): array
    {
        [$range, $start, $end] = $this->resolveRange($requestedRange);
        $orders = $this->ordersInRange($start, $end);
        $statusCounts = $this->summary(clone $orders, 'order_status', [
            'pending', 'confirmed', 'processing', 'shipped', 'completed', 'cancelled',
        ]);
        $paymentCounts = $this->summary(clone $orders, 'payment_status', [
            'unpaid', 'pending', 'paid', 'failed', 'refunded', 'cancelled',
        ]);
        $lowStock = $this->stockItems('low', 6);
        $lowStockCount = $this->stockQuery('low')->count();
        $outOfStockCount = $this->stockQuery('out')->count();
        $pendingOrders = $statusCounts['pending'];
        $unpaidCodOrders = (clone $orders)
            ->where('payment_method', 'cod')
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->count();
        $baseCurrency = $this->currencyService->getDefault();
        $grossRevenue = (float) (clone $orders)
            ->where('order_status', '!=', 'cancelled')
            ->selectRaw('COALESCE(SUM(total_amount * exchange_rate), 0) as aggregate')
            ->value('aggregate');

        return [
            'range' => $range,
            'rangeStart' => $start,
            'rangeEnd' => $end,
            'baseCurrency' => $baseCurrency,
            'kpis' => [
                'revenue' => $this->formatMoney($grossRevenue, $baseCurrency),
                'orders' => (clone $orders)->count(),
                'pending_orders' => $pendingOrders,
                'unpaid_cod_orders' => $unpaidCodOrders,
                'products' => Product::query()->active()->count(),
                'low_stock' => $lowStockCount,
            ],
            'statusCounts' => $statusCounts,
            'paymentCounts' => $paymentCounts,
            'recentOrders' => (clone $orders)->latest('placed_at')->latest('id')->limit(8)->get(),
            'lowStockItems' => $lowStock,
            'topProducts' => $this->topProducts($start, $end, $baseCurrency),
            'alerts' => collect([
                ['type' => 'pending', 'count' => $pendingOrders, 'href' => route('admin.orders.index', ['order_status' => 'pending'])],
                ['type' => 'cod', 'count' => $unpaidCodOrders, 'href' => route('admin.orders.index', ['payment_status' => 'unpaid', 'payment_method' => 'cod'])],
                ['type' => 'low_stock', 'count' => $lowStockCount, 'href' => route('admin.inventory.index', ['stock_status' => 'low_stock'])],
                ['type' => 'out_of_stock', 'count' => $outOfStockCount, 'href' => route('admin.inventory.index', ['stock_status' => 'out_of_stock'])],
            ])->filter(fn (array $alert): bool => $alert['count'] > 0)->values(),
        ];
    }

    public function formatOrderMoney(Order $order): string
    {
        $currency = $this->currencyService->findByCode($order->currency_code);

        if ($currency) {
            return $this->currencyService->format((float) $order->total_amount, $currency);
        }

        return number_format((float) $order->total_amount, $order->currency_decimal_places ?? 0).' '.$order->currency_code;
    }

    private function resolveRange(?string $requestedRange): array
    {
        $range = in_array($requestedRange, self::RANGES, true) ? $requestedRange : self::DEFAULT_RANGE;
        $now = CarbonImmutable::now();
        $start = match ($range) {
            'today' => $now->startOfDay(),
            'last30' => $now->subDays(29)->startOfDay(),
            'this_month' => $now->startOfMonth(),
            default => $now->subDays(6)->startOfDay(),
        };

        return [$range, $start, $now->endOfDay()];
    }

    private function ordersInRange(CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        return Order::query()->whereBetween(
            DB::raw('COALESCE(placed_at, created_at)'),
            [$start, $end],
        );
    }

    private function summary(Builder $query, string $column, array $statuses): array
    {
        $counts = $query->select($column, DB::raw('COUNT(*) as aggregate'))
            ->groupBy($column)
            ->pluck('aggregate', $column);

        return collect($statuses)
            ->mapWithKeys(fn (string $status): array => [$status => (int) ($counts[$status] ?? 0)])
            ->all();
    }

    private function stockQuery(string $status): Builder
    {
        return InventoryStock::query()
            ->where(function (Builder $query): void {
                $query->whereNotNull('product_variant_id')
                    ->orWhereHas('product', fn (Builder $query) => $query->whereDoesntHave('productVariants'));
            })
            ->whereHas('product')
            ->when(
                $status === 'low',
                fn (Builder $query) => $query
                    ->whereRaw('(quantity - reserved_quantity) > 0')
                    ->whereRaw('(quantity - reserved_quantity) <= low_stock_threshold'),
                fn (Builder $query) => $query->whereRaw('(quantity - reserved_quantity) <= 0'),
            );
    }

    private function stockItems(string $status, int $limit): Collection
    {
        return $this->stockQuery($status)
            ->with(['product.productTranslations', 'productVariant'])
            ->orderByRaw('(quantity - reserved_quantity) ASC')
            ->limit($limit)
            ->get();
    }

    private function topProducts(CarbonImmutable $start, CarbonImmutable $end, ?Currency $baseCurrency): Collection
    {
        $items = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween(DB::raw('COALESCE(orders.placed_at, orders.created_at)'), [$start, $end])
            ->where('orders.order_status', '!=', 'cancelled')
            ->select([
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('COALESCE(order_items.sku, order_items.product_sku) as snapshot_sku'),
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.total * orders.exchange_rate) as revenue'),
            ])
            ->groupBy('order_items.product_id', 'order_items.product_name', DB::raw('COALESCE(order_items.sku, order_items.product_sku)'))
            ->orderByDesc('quantity_sold')
            ->limit(5)
            ->get();
        $products = Product::query()->whereIn('id', $items->pluck('product_id')->filter())->get()->keyBy('id');

        return $items->map(function ($item) use ($products, $baseCurrency) {
            $item->formatted_revenue = $this->formatMoney((float) $item->revenue, $baseCurrency);
            $item->product_url = $products->has($item->product_id)
                ? route('admin.products.edit', $item->product_id)
                : null;

            return $item;
        });
    }

    private function formatMoney(float $amount, ?Currency $currency): string
    {
        return $currency
            ? $this->currencyService->format($amount, $currency)
            : number_format($amount, 0);
    }
}
