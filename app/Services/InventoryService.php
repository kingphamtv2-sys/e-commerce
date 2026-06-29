<?php

namespace App\Services;

use App\Models\InventoryLog;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryService
{
    public function syncStockRecords(?User $actor = null): void
    {
        Product::query()->with('productVariants')->each(function (Product $product) use ($actor): void {
            if ($product->productVariants->isEmpty()) {
                $this->firstOrCreateStock($product, null, $actor);

                return;
            }

            foreach ($product->productVariants as $variant) {
                $this->firstOrCreateStock($product, $variant->id, $actor);
            }
        });
    }

    public function ensureVariantStock(ProductVariant $variant, ?User $actor = null): InventoryStock
    {
        return $this->firstOrCreateStock($variant->product, $variant->id, $actor);
    }

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $stocks = $this->validStocks();
        $keyword = Str::lower(trim((string) ($filters['keyword'] ?? '')));

        if ($keyword !== '') {
            $stocks = $stocks->filter(function (InventoryStock $stock) use ($keyword): bool {
                $productName = $stock->product
                    ? app(ProductService::class)->name($stock->product)
                    : '';

                return Str::contains(Str::lower($productName), $keyword)
                    || Str::contains(Str::lower($this->sku($stock)), $keyword);
            });
        }

        if (! empty($filters['category_id'])) {
            $stocks = $stocks->filter(fn (InventoryStock $stock): bool => $stock->product?->category_id === (int) $filters['category_id']);
        }

        if (! empty($filters['stock_status'])) {
            $stocks = $stocks->filter(fn (InventoryStock $stock): bool => $stock->stockStatus() === $filters['stock_status']);
        }

        if (! empty($filters['product_type'])) {
            $wantVariant = $filters['product_type'] === 'variant';
            $stocks = $stocks->filter(fn (InventoryStock $stock): bool => ($stock->product_variant_id !== null) === $wantVariant);
        }

        $page = Paginator::resolveCurrentPage();

        return new Paginator(
            $stocks->forPage($page, $perPage)->values(),
            $stocks->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => request()->query()],
        );
    }

    public function adjust(InventoryStock $stock, array $data, User $actor): InventoryStock
    {
        return DB::transaction(function () use ($stock, $data, $actor): InventoryStock {
            $locked = InventoryStock::query()->lockForUpdate()->findOrFail($stock->id);
            $before = $locked->quantity;
            $amount = (int) $data['quantity'];
            $after = match ($data['adjustment_type']) {
                'increase' => $before + $amount,
                'decrease' => $before - $amount,
                'set' => $amount,
            };

            if ($after < 0 || $after < $locked->reserved_quantity) {
                throw new DomainException(__('admin.messages.inventory_below_reserved'));
            }

            $locked->update([
                'quantity' => $after,
                'low_stock_threshold' => $data['low_stock_threshold'],
            ]);
            $locked->inventoryLogs()->create([
                'product_id' => $locked->product_id,
                'product_variant_id' => $locked->product_variant_id,
                'type' => $data['adjustment_type'],
                'quantity_before' => $before,
                'quantity_change' => $after - $before,
                'quantity_after' => $after,
                'reason' => $data['reason'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => $actor->id,
            ]);

            return $locked->refresh();
        });
    }

    /** @return Collection<int, InventoryLog> */
    public function logs(InventoryStock $stock, int $limit = 100): Collection
    {
        return $stock->inventoryLogs()->with('createdBy')->latest()->limit($limit)->get();
    }

    public function sku(InventoryStock $stock): string
    {
        return $stock->productVariant?->sku ?? $stock->product?->sku ?? '—';
    }

    public function variantName(InventoryStock $stock): string
    {
        return $stock->productVariant?->name ?? '—';
    }

    private function validStocks(): Collection
    {
        return InventoryStock::query()
            ->with([
                'product.productTranslations',
                'product.category.categoryTranslations',
                'product.productVariants',
                'productVariant',
            ])
            ->latest('updated_at')
            ->get()
            ->filter(function (InventoryStock $stock): bool {
                if (! $stock->product) {
                    return false;
                }

                return $stock->product_variant_id !== null
                    ? $stock->productVariant !== null
                    : $stock->product->productVariants->isEmpty();
            })
            ->values();
    }

    private function firstOrCreateStock(Product $product, ?int $variantId, ?User $actor): InventoryStock
    {
        return DB::transaction(function () use ($product, $variantId, $actor): InventoryStock {
            $stock = InventoryStock::query()
                ->where('product_id', $product->id)
                ->when($variantId, fn ($query) => $query->where('product_variant_id', $variantId), fn ($query) => $query->whereNull('product_variant_id'))
                ->lockForUpdate()
                ->first();

            if ($stock) {
                return $stock;
            }

            $stock = InventoryStock::query()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
                'quantity' => 0,
                'reserved_quantity' => 0,
                'low_stock_threshold' => 5,
            ]);
            $stock->inventoryLogs()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
                'type' => 'initial',
                'quantity_before' => 0,
                'quantity_change' => 0,
                'quantity_after' => 0,
                'reason' => __('admin.inventory.initial_reason'),
                'created_by' => $actor?->id,
            ]);

            return $stock;
        });
    }
}
