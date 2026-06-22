<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\VariantImage;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class VariantImageService
{
    /** @return Collection<int, VariantImage> */
    public function images(ProductVariant $variant): Collection
    {
        return $variant->variantImages()->orderBy('sort_order')->orderBy('id')->get();
    }

    /** @param array<int, UploadedFile> $files */
    public function upload(ProductVariant $variant, array $files, array $data): Collection
    {
        $storedPaths = [];
        $createdImages = collect();
        try {
            DB::transaction(function () use ($variant, $files, $data, &$storedPaths, $createdImages): void {
                $nextOrder = isset($data['sort_order']) ? (int) $data['sort_order'] : ((int) $variant->variantImages()->max('sort_order')) + 1;
                $hasMain = $variant->variantImages()->active()->where('is_main', true)->exists();
                foreach ($files as $index => $file) {
                    $path = $file->store("variant-images/{$variant->id}", 'public');
                    if (! is_string($path)) {
                        throw new RuntimeException('Unable to store variant image.');
                    }
                    $storedPaths[] = $path;
                    $makeMain = $data['status'] && $index === 0 && ($data['is_main'] || ! $hasMain);
                    if ($makeMain) {
                        $variant->variantImages()->update(['is_main' => false]);
                        $hasMain = true;
                    }
                    $createdImages->push($variant->variantImages()->create([
                        'image_path' => $path, 'alt_text' => $data['alt_text'] ?? null,
                        'sort_order' => $nextOrder + $index, 'status' => $data['status'], 'is_main' => $makeMain,
                    ]));
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);
            throw $exception;
        }
        $this->clearCache();

        return $createdImages;
    }

    public function update(VariantImage $image, array $data): void
    {
        if ($data['is_main'] && ! $data['status']) {
            throw new DomainException(__('admin.messages.inactive_variant_image_cannot_main'));
        }
        DB::transaction(function () use ($image, $data): void {
            $wasMain = $image->is_main;
            $image->update($data);
            if ($data['is_main']) {
                $this->unsetOtherMainImages($image);
            } elseif ($wasMain) {
                $this->promoteFallback($image->productVariant);
            }
        });
        $this->clearCache();
    }

    public function setMain(VariantImage $image): void
    {
        if (! $image->status) {
            throw new DomainException(__('admin.messages.inactive_variant_image_cannot_main'));
        }
        DB::transaction(function () use ($image): void {
            $this->unsetOtherMainImages($image);
            $image->forceFill(['is_main' => true])->save();
        });
        $this->clearCache();
    }

    public function delete(VariantImage $image): void
    {
        $path = $image->image_path;
        $variant = $image->productVariant;
        $wasMain = $image->is_main;
        DB::transaction(function () use ($image, $variant, $wasMain): void {
            $image->delete();
            if ($wasMain) {
                $this->promoteFallback($variant);
            }
        });
        Storage::disk('public')->delete($path);
        $this->clearCache();
    }

    public function url(VariantImage $image): string
    {
        return '/storage/'.ltrim($image->image_path, '/');
    }

    public function exists(VariantImage $image): bool
    {
        return Storage::disk('public')->exists($image->image_path);
    }

    private function unsetOtherMainImages(VariantImage $image): void
    {
        $image->productVariant->variantImages()->whereKeyNot($image->id)->update(['is_main' => false]);
    }

    private function promoteFallback(ProductVariant $variant): void
    {
        $variant->variantImages()->update(['is_main' => false]);
        $fallback = $variant->variantImages()->active()->orderBy('sort_order')->orderBy('id')->first();
        $fallback?->forceFill(['is_main' => true])->save();
    }

    private function clearCache(): void
    {
        app(ProductService::class)->clearCache();
    }
}
