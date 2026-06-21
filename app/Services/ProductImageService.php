<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProductImageService
{
    /** @return Collection<int, ProductImage> */
    public function images(Product $product): Collection
    {
        return $product->productImages()->orderBy('sort_order')->orderBy('id')->get();
    }

    public function mainImage(Product $product): ?ProductImage
    {
        return $product->productImages()->active()->orderByDesc('is_main')->orderBy('sort_order')->orderBy('id')->first();
    }

    /** @param array<int, UploadedFile> $files */
    public function upload(Product $product, array $files, array $data): void
    {
        $storedPaths = [];

        try {
            DB::transaction(function () use ($product, $files, $data, &$storedPaths): void {
                $nextSortOrder = isset($data['sort_order'])
                    ? (int) $data['sort_order']
                    : ((int) $product->productImages()->max('sort_order')) + 1;
                $hasActiveMain = $product->productImages()->active()->where('is_main', true)->exists();

                foreach ($files as $index => $file) {
                    $path = $file->store("product-images/{$product->id}", 'public');

                    if (! is_string($path)) {
                        throw new RuntimeException('Unable to store product image.');
                    }

                    $storedPaths[] = $path;
                    $makeMain = (bool) $data['status'] && ($index === 0) && ((bool) $data['is_main'] || ! $hasActiveMain);

                    if ($makeMain) {
                        $product->productImages()->update(['is_main' => false]);
                        $hasActiveMain = true;
                    }

                    $product->productImages()->create([
                        'image_path' => $path,
                        'alt_text' => $data['alt_text'] ?? null,
                        'sort_order' => $nextSortOrder + $index,
                        'status' => $data['status'],
                        'is_main' => $makeMain,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }

        $this->clearCache();
    }

    public function update(ProductImage $productImage, array $data): void
    {
        if ($data['is_main'] && ! $data['status']) {
            throw new DomainException(__('admin.messages.inactive_image_cannot_main'));
        }

        DB::transaction(function () use ($productImage, $data): void {
            $wasMain = $productImage->is_main;
            $productImage->update([
                'alt_text' => $data['alt_text'] ?? null,
                'sort_order' => $data['sort_order'],
                'status' => $data['status'],
                'is_main' => $data['is_main'],
            ]);

            if ($data['is_main']) {
                $this->unsetOtherMainImages($productImage);
            } elseif ($wasMain) {
                $this->promoteFallback($productImage->product);
            }
        });

        $this->clearCache();
    }

    public function setMain(ProductImage $productImage): void
    {
        if (! $productImage->status) {
            throw new DomainException(__('admin.messages.inactive_image_cannot_main'));
        }

        DB::transaction(function () use ($productImage): void {
            $this->unsetOtherMainImages($productImage);
            $productImage->forceFill(['is_main' => true])->save();
        });

        $this->clearCache();
    }

    public function delete(ProductImage $productImage): void
    {
        $path = $productImage->image_path;
        $product = $productImage->product;
        $wasMain = $productImage->is_main;

        DB::transaction(function () use ($productImage, $product, $wasMain): void {
            $productImage->delete();

            if ($wasMain) {
                $this->promoteFallback($product);
            }
        });

        Storage::disk('public')->delete($path);
        $this->clearCache();
    }

    public function url(ProductImage $productImage): string
    {
        return '/storage/'.ltrim($productImage->image_path, '/');
    }

    public function exists(ProductImage $productImage): bool
    {
        return Storage::disk('public')->exists($productImage->image_path);
    }

    private function unsetOtherMainImages(ProductImage $productImage): void
    {
        $productImage->product->productImages()->whereKeyNot($productImage->id)->update(['is_main' => false]);
    }

    private function promoteFallback(Product $product): void
    {
        $product->productImages()->update(['is_main' => false]);
        $fallback = $product->productImages()->active()->orderBy('sort_order')->orderBy('id')->first();
        $fallback?->forceFill(['is_main' => true])->save();
    }

    private function clearCache(): void
    {
        app(ProductService::class)->clearCache();
    }
}
