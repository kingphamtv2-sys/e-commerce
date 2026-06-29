<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Models\User;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductVariantService
{
    public function createOption(Product $product, array $data): ProductOption
    {
        return $product->productOptions()->create($data);
    }

    public function updateOption(ProductOption $option, array $data): ProductOption
    {
        $option->update($data);

        return $option->refresh();
    }

    public function deleteOption(ProductOption $option): void
    {
        if ($option->variantOptionValues()->exists()) {
            throw new DomainException(__('admin.messages.option_in_use'));
        }

        $option->delete();
    }

    public function createValue(ProductOption $option, array $data): ProductOptionValue
    {
        return $option->values()->create($data);
    }

    public function updateValue(ProductOptionValue $value, array $data): ProductOptionValue
    {
        $value->update($data);

        return $value->refresh();
    }

    public function deleteValue(ProductOptionValue $value): void
    {
        if ($value->variantOptionValues()->exists()) {
            throw new DomainException(__('admin.messages.option_value_in_use'));
        }

        $value->delete();
    }

    public function createVariant(Product $product, array $data, ?User $actor = null): ProductVariant
    {
        return DB::transaction(function () use ($product, $data, $actor): ProductVariant {
            $values = $this->selectedValues($product, $data['option_values']);
            $this->assertCombinationUnique($product, $values);
            $variant = $product->productVariants()->create($this->variantData($data, $values));
            $this->syncOptionValues($variant, $values);
            app(InventoryService::class)->ensureVariantStock($variant, $actor);
            $this->clearCaches();

            return $variant->load('optionValues.option');
        });
    }

    public function updateVariant(ProductVariant $variant, array $data): ProductVariant
    {
        return DB::transaction(function () use ($variant, $data): ProductVariant {
            $values = $this->selectedValues($variant->product, $data['option_values']);
            $this->assertCombinationUnique($variant->product, $values, $variant);
            $variant->update($this->variantData($data, $values));
            $this->syncOptionValues($variant, $values);
            $this->clearCaches();

            return $variant->refresh()->load('optionValues.option');
        });
    }

    public function deleteVariant(ProductVariant $variant): void
    {
        $stock = $variant->inventoryStock()->withCount('inventoryLogs')->first();
        if ($variant->orderItems()->exists() || $variant->variantImages()->exists() || ($stock && ($stock->quantity !== 0 || $stock->reserved_quantity !== 0 || $stock->inventory_logs_count > 1))) {
            throw new DomainException(__('admin.messages.variant_has_history'));
        }

        $imagePaths = $variant->variantImages()->pluck('image_path')->all();
        $variant->forceDelete();
        Storage::disk('public')->delete($imagePaths);
        $this->clearCaches();
    }

    /** @return Collection<int, ProductOptionValue> */
    private function selectedValues(Product $product, array $selection): Collection
    {
        $options = $product->productOptions()->active()->with(['values' => fn ($query) => $query->active()])->get();

        return $options->map(function (ProductOption $option) use ($selection): ProductOptionValue {
            return $option->values->firstWhere('id', (int) ($selection[$option->id] ?? 0))
                ?? throw new DomainException(__('admin.messages.invalid_variant_option_value'));
        });
    }

    /** @param Collection<int, ProductOptionValue> $values */
    private function assertCombinationUnique(Product $product, Collection $values, ?ProductVariant $except = null): void
    {
        $wanted = $values->pluck('id')->sort()->values()->all();
        $duplicate = $product->productVariants()
            ->when($except, fn ($query) => $query->whereKeyNot($except->id))
            ->with('optionValues:id')
            ->get()
            ->contains(fn (ProductVariant $variant): bool => $variant->optionValues->pluck('id')->sort()->values()->all() === $wanted);

        if ($duplicate) {
            throw new DomainException(__('admin.messages.duplicate_variant_combination'));
        }
    }

    /** @param Collection<int, ProductOptionValue> $values */
    private function variantData(array $data, Collection $values): array
    {
        return [
            'sku' => $data['sku'],
            'name' => filled($data['name'] ?? null) ? $data['name'] : $values->map->label()->implode(' / '),
            'price' => filled($data['price'] ?? null) ? $data['price'] : null,
            'sale_price' => filled($data['sale_price'] ?? null) ? $data['sale_price'] : null,
            'status' => $data['status'],
        ];
    }

    /** @param Collection<int, ProductOptionValue> $values */
    private function syncOptionValues(ProductVariant $variant, Collection $values): void
    {
        $variant->variantOptionValues()->delete();
        foreach ($values as $value) {
            $variant->variantOptionValues()->create([
                'product_option_id' => $value->product_option_id,
                'product_option_value_id' => $value->id,
            ]);
        }
    }

    private function clearCaches(): void
    {
        app(ProductService::class)->clearCache();
    }
}
