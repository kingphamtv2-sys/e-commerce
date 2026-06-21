<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\InventoryStock;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductDetailService
{
    public function findVisibleBySlug(string $slug, Language $language, Language $defaultLanguage): Product
    {
        return Product::query()
            ->active()
            ->whereHas('category', fn (Builder $query) => $query->active())
            ->whereHas('productTranslations', fn (Builder $query) => $query
                ->where('slug', $slug)
                ->whereIn('language_code', array_unique([$language->code, $defaultLanguage->code])))
            ->with([
                'productTranslations',
                'category.categoryTranslations',
                'productImages' => fn ($query) => $query->active()->orderByDesc('is_main')->orderBy('sort_order')->orderBy('id'),
                'productVariants' => fn ($query) => $query->where('status', true)->orderBy('id'),
                'inventoryStocks',
            ])
            ->firstOrFail();
    }

    public function translation(Product $product, Language $language): ?ProductTranslation
    {
        return app(ProductService::class)->translation($product, $language->code);
    }

    /** @return Collection<int, Product> */
    public function relatedProducts(Product $product, int $limit = 4): Collection
    {
        return Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->getKey())
            ->whereHas('category', fn (Builder $query) => $query->active())
            ->whereHas('productTranslations')
            ->with([
                'productTranslations',
                'category.categoryTranslations',
                'productImages' => fn ($query) => $query->active()->orderByDesc('is_main')->orderBy('sort_order')->orderBy('id'),
                'productVariants' => fn ($query) => $query->where('status', true),
                'inventoryStocks',
            ])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /** @return array<int, array<string, mixed>> */
    public function variantOptions(Product $product, Currency $currency, Currency $baseCurrency): array
    {
        return $product->productVariants->map(function (ProductVariant $variant) use ($product, $currency, $baseCurrency): array {
            $regularPrice = (float) ($variant->price ?? $product->price);
            $saleCandidate = $variant->sale_price ?? $product->sale_price;
            $salePrice = $saleCandidate !== null && (float) $saleCandidate < $regularPrice
                ? (float) $saleCandidate
                : null;
            $stock = $product->inventoryStocks->firstWhere('product_variant_id', $variant->id);

            return [
                'id' => $variant->id,
                'name' => $variant->name,
                'sku' => $variant->sku,
                'price' => app(CatalogService::class)->formatPrice($salePrice ?? $regularPrice, $currency, $baseCurrency),
                'original_price' => $salePrice !== null
                    ? app(CatalogService::class)->formatPrice($regularPrice, $currency, $baseCurrency)
                    : null,
                'stock_status' => $stock?->stockStatus() ?? 'out_of_stock',
                'available_quantity' => $stock?->availableQuantity() ?? 0,
            ];
        })->values()->all();
    }

    public function availableQuantity(Product $product): int
    {
        $variantIds = $product->productVariants->pluck('id');
        $stocks = $variantIds->isNotEmpty()
            ? $product->inventoryStocks->whereIn('product_variant_id', $variantIds)
            : $product->inventoryStocks->whereNull('product_variant_id');

        return $stocks->sum(fn (InventoryStock $stock): int => $stock->availableQuantity());
    }
}
