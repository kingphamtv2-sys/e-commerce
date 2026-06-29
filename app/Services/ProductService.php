<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    public const CACHE_ALL = 'products_all';

    /** @return array<int, Product> */
    public function all(): array
    {
        return Cache::rememberForever(
            self::CACHE_ALL,
            static fn (): array => Product::query()
                ->with(['productTranslations', 'category.categoryTranslations', 'taxClass', 'productVariants'])
                ->latest()
                ->get()
                ->all(),
        );
    }

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $products = collect($this->all());
        $keyword = Str::lower(trim((string) ($filters['keyword'] ?? '')));

        if ($keyword !== '') {
            $products = $products->filter(fn (Product $product): bool => Str::contains(Str::lower($product->sku), $keyword)
                || $product->productTranslations->contains(
                    fn (ProductTranslation $translation): bool => Str::contains(Str::lower($translation->name), $keyword),
                ));
        }

        foreach (['category_id', 'status', 'is_featured'] as $filter) {
            if (isset($filters[$filter]) && $filters[$filter] !== '') {
                $value = $filter === 'category_id' ? (int) $filters[$filter] : (bool) $filters[$filter];
                $products = $products->where($filter, $value);
            }
        }

        if (($filters['sort'] ?? '') === 'price_asc') {
            $products = $products->sortBy(fn (Product $product): float => (float) $product->price);
        } elseif (($filters['sort'] ?? '') === 'price_desc') {
            $products = $products->sortByDesc(fn (Product $product): float => (float) $product->price);
        }

        $page = Paginator::resolveCurrentPage();

        $paginator = new Paginator(
            $products->forPage($page, $perPage)->values(),
            $products->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => request()->query()],
        );

        /** @var EloquentCollection<int, Product> $pageProducts */
        $pageProducts = new EloquentCollection($paginator->items());
        $pageProducts->loadMissing([
            'productImages' => fn ($query) => $query->active()->orderByDesc('is_main')->orderBy('sort_order')->orderBy('id'),
            'inventoryStocks',
        ]);
        $paginator->setCollection($pageProducts);

        return $paginator;
    }

    public function availableStock(Product $product): int
    {
        $variantIds = $product->productVariants->pluck('id');
        $stocks = $variantIds->isNotEmpty()
            ? $product->inventoryStocks->whereIn('product_variant_id', $variantIds)
            : $product->inventoryStocks->whereNull('product_variant_id');

        return $stocks->sum(fn ($stock): int => $stock->availableQuantity());
    }

    public function stockStatus(Product $product): string
    {
        $variantIds = $product->productVariants->pluck('id');
        $stocks = $variantIds->isNotEmpty()
            ? $product->inventoryStocks->whereIn('product_variant_id', $variantIds)
            : $product->inventoryStocks->whereNull('product_variant_id');

        if ($stocks->isEmpty() || $this->availableStock($product) === 0) {
            return 'out_of_stock';
        }

        return $stocks->contains(fn ($stock): bool => $stock->stockStatus() === 'low_stock')
            ? 'low_stock'
            : 'in_stock';
    }

    public function translation(Product $product, ?string $languageCode = null): ?ProductTranslation
    {
        $translations = $product->relationLoaded('productTranslations')
            ? $product->productTranslations
            : $product->productTranslations()->get();
        $defaultCode = app(LanguageService::class)->getDefault()?->code;

        return $translations->firstWhere('language_code', $languageCode ?: $defaultCode)
            ?? $translations->firstWhere('language_code', $defaultCode)
            ?? $translations->first();
    }

    public function name(Product $product, ?string $languageCode = null): string
    {
        return $this->translation($product, $languageCode)?->name ?? '—';
    }

    public function create(array $data): Product
    {
        $product = DB::transaction(function () use ($data): Product {
            $product = Product::query()->create($this->generalData($data));
            $this->syncTranslations($product, $data['translations']);
            if (array_key_exists('variants', $data)) {
                $this->syncVariants($product, $data['variants']);
            }

            return $product;
        });

        $this->clearRelatedCaches();

        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        DB::transaction(function () use ($product, $data): void {
            $product->update($this->generalData($data));
            $this->syncTranslations($product, $data['translations']);
            if (array_key_exists('variants', $data)) {
                $this->syncVariants($product, $data['variants']);
            }
        });

        $this->clearRelatedCaches();

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product): void {
            $product->productVariants()->delete();
            $product->delete();
        });

        $this->clearRelatedCaches();
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_ALL);
    }

    private function generalData(array $data): array
    {
        return [
            'category_id' => $data['category_id'],
            'tax_class_id' => $data['tax_class_id'] ?? null,
            'sku' => $data['sku'],
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'cost_price' => $data['cost_price'] ?? null,
            'status' => $data['status'],
            'is_featured' => $data['is_featured'],
        ];
    }

    private function syncTranslations(Product $product, array $translations): void
    {
        foreach ($translations as $languageCode => $translation) {
            if (blank($translation['name'] ?? null)) {
                $product->productTranslations()->where('language_code', $languageCode)->delete();

                continue;
            }

            $product->productTranslations()->updateOrCreate(
                ['language_code' => $languageCode],
                [
                    'name' => $translation['name'],
                    'slug' => $translation['slug'],
                    'short_description' => $translation['short_description'] ?? null,
                    'description' => $translation['description'] ?? null,
                    'meta_title' => $translation['meta_title'] ?? null,
                    'meta_description' => $translation['meta_description'] ?? null,
                ],
            );
        }
    }

    private function syncVariants(Product $product, array $variants): void
    {
        $keptIds = [];

        foreach ($variants as $variant) {
            $attributes = [
                'sku' => $variant['sku'],
                'name' => $variant['name'],
                'price' => $variant['price'] ?? null,
                'sale_price' => $variant['sale_price'] ?? null,
                'status' => $variant['status'],
            ];

            if (! empty($variant['id'])) {
                $model = $product->productVariants()->findOrFail($variant['id']);
                $model->update($attributes);
            } else {
                $model = $product->productVariants()->create($attributes);
            }

            $keptIds[] = $model->id;
        }

        $product->productVariants()->whereNotIn('id', $keptIds)->delete();
    }

    private function clearRelatedCaches(): void
    {
        $this->clearCache();
        app(CategoryService::class)->clearCache();
    }
}
