<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Currency;
use App\Models\InventoryStock;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductTranslation;
use App\Models\VariantImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CatalogService
{
    public function products(array $filters, Language $language, Language $defaultLanguage): LengthAwarePaginator
    {
        $query = Product::query()
            ->active()
            ->whereHas('category', fn (Builder $query) => $query->active())
            ->whereHas('productTranslations')
            ->with([
                'productTranslations',
                'category.categoryTranslations',
                'productImages' => fn ($query) => $query->active()->orderByDesc('is_main')->orderBy('sort_order')->orderBy('id'),
                'productVariants' => fn ($query) => $query->where('status', true)->select(['id', 'product_id', 'sku', 'name', 'status'])->with(['variantImages' => fn ($query) => $query->active()]),
                'inventoryStocks',
            ]);

        $keyword = $filters['keyword'] ?? null;
        if ($keyword) {
            $query->where(function (Builder $query) use ($keyword, $language, $defaultLanguage): void {
                $query->where('sku', 'like', "%{$keyword}%")
                    ->orWhereHas('productTranslations', function (Builder $query) use ($keyword, $language, $defaultLanguage): void {
                        $query->whereIn('language_code', array_unique([$language->code, $defaultLanguage->code]))
                            ->where(function (Builder $query) use ($keyword): void {
                                $query->where('name', 'like', "%{$keyword}%")
                                    ->orWhere('short_description', 'like', "%{$keyword}%");
                            });
                    });
            });
        }

        $selectedCategory = $this->resolveCategory($filters['category'] ?? null, $language, $defaultLanguage);
        if (! empty($filters['category'])) {
            if ($selectedCategory) {
                $query->whereIn('category_id', [$selectedCategory->id, ...$this->activeDescendantIds($selectedCategory)]);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if (isset($filters['min_price'])) {
            $this->applyPriceBoundary($query, '>=', (float) $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $this->applyPriceBoundary($query, '<=', (float) $filters['max_price']);
        }
        if (! empty($filters['stock'])) {
            $this->applyStockFilter($query, $filters['stock']);
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderByRaw('COALESCE(sale_price, price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, price) DESC'),
            'name_asc' => $query->orderBy(
                ProductTranslation::query()
                    ->select('name')
                    ->whereColumn('product_id', 'products.id')
                    ->where('language_code', $language->code)
                    ->limit(1),
            ),
            'featured' => $query->orderByDesc('is_featured')->latest(),
            default => $query->latest(),
        };

        return $query->paginate(12)->withQueryString();
    }

    /** @return Collection<int, Category> */
    public function categories(): Collection
    {
        return Category::query()
            ->active()
            ->with('categoryTranslations')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function resolveCategory(?string $value, Language $language, Language $defaultLanguage): ?Category
    {
        if (! $value) {
            return null;
        }

        return Category::query()
            ->active()
            ->with('categoryTranslations')
            ->where(function (Builder $query) use ($value, $language, $defaultLanguage): void {
                if (ctype_digit($value)) {
                    $query->whereKey((int) $value);
                }

                $query->orWhereHas('categoryTranslations', function (Builder $query) use ($value, $language, $defaultLanguage): void {
                    $query->where('slug', $value)
                        ->whereIn('language_code', array_unique([$language->code, $defaultLanguage->code]));
                });
            })
            ->first();
    }

    public function productName(Product $product, Language $language): string
    {
        return app(ProductService::class)->name($product, $language->code);
    }

    public function productSlug(Product $product, Language $language): string
    {
        return app(ProductService::class)->translation($product, $language->code)?->slug
            ?? (string) $product->getKey();
    }

    public function shortDescription(Product $product, Language $language): ?string
    {
        return app(ProductService::class)->translation($product, $language->code)?->short_description;
    }

    public function categoryName(Category $category, Language $language): string
    {
        return app(CategoryService::class)->name($category, $language->code);
    }

    public function categorySlug(Category $category, Language $language): ?string
    {
        return app(CategoryService::class)->translation($category, $language->code)?->slug;
    }

    public function mainImage(Product $product): ProductImage|VariantImage|null
    {
        return $product->productImages->first()
            ?? $product->productVariants->first(fn ($variant) => $variant->variantImages->isNotEmpty())?->variantImages->first();
    }

    public function imageUrl(ProductImage|VariantImage $image): string
    {
        return $image instanceof VariantImage
            ? app(VariantImageService::class)->url($image)
            : app(ProductImageService::class)->url($image);
    }

    public function formatPrice(float|int|string $amount, Currency $currency, Currency $baseCurrency): string
    {
        $converted = app(CurrencyService::class)->convert((float) $amount, $baseCurrency, $currency);

        return app(CurrencyService::class)->format($converted, $currency);
    }

    public function stockStatus(Product $product): string
    {
        $hasVariants = $product->productVariants->isNotEmpty();
        $stocks = $product->inventoryStocks->filter(
            fn (InventoryStock $stock): bool => $hasVariants
                ? $stock->product_variant_id !== null
                : $stock->product_variant_id === null,
        );

        if ($stocks->isEmpty() || $stocks->sum(fn (InventoryStock $stock): int => $stock->availableQuantity()) === 0) {
            return 'out_of_stock';
        }

        return $stocks->contains(fn (InventoryStock $stock): bool => $stock->stockStatus() === 'low_stock')
            ? 'low_stock'
            : 'in_stock';
    }

    public function discountPercentage(Product $product): ?int
    {
        if ($product->sale_price === null || (float) $product->sale_price >= (float) $product->price || (float) $product->price <= 0) {
            return null;
        }

        return (int) round((1 - ((float) $product->sale_price / (float) $product->price)) * 100);
    }

    /** @return array<int, int> */
    private function activeDescendantIds(Category $category): array
    {
        $ids = [];
        $pending = [$category->id];

        while ($pending !== []) {
            $children = Category::query()->active()->whereIn('parent_id', $pending)->pluck('id')->all();
            $newIds = array_values(array_diff($children, $ids));
            $ids = [...$ids, ...$newIds];
            $pending = $newIds;
        }

        return $ids;
    }

    private function applyStockFilter(Builder $query, string $status): void
    {
        if ($status === 'out_of_stock') {
            $query->whereDoesntHave('inventoryStocks', fn (Builder $query) => $query->whereRaw('quantity - reserved_quantity > 0'));

            return;
        }

        $query->whereHas('inventoryStocks', function (Builder $query) use ($status): void {
            $query->whereRaw('quantity - reserved_quantity > 0');

            if ($status === 'low_stock') {
                $query->whereRaw('quantity - reserved_quantity <= low_stock_threshold');
            } else {
                $query->whereRaw('quantity - reserved_quantity > low_stock_threshold');
            }
        });
    }

    private function applyPriceBoundary(Builder $query, string $operator, float $amount): void
    {
        $query->where(function (Builder $query) use ($operator, $amount): void {
            $query->where(function (Builder $query) use ($operator, $amount): void {
                $query->whereNotNull('sale_price')->where('sale_price', $operator, $amount);
            })->orWhere(function (Builder $query) use ($operator, $amount): void {
                $query->whereNull('sale_price')->where('price', $operator, $amount);
            });
        });
    }
}
