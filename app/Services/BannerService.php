<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\BannerTranslation;
use App\Models\Language;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BannerService
{
    public const POSITIONS = [
        'home_hero',
        'home_top',
        'home_middle',
        'home_bottom',
        'catalog_top',
        'category_top',
        'product_detail',
        'sidebar',
        'header_announcement',
    ];

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Banner::query()
            ->with('translations')
            ->when($filters['keyword'] ?? null, function ($query, string $keyword): void {
                $query->where(function ($query) use ($keyword): void {
                    $query->where('position', 'like', "%{$keyword}%")
                        ->orWhere('link_url', 'like', "%{$keyword}%")
                        ->orWhereHas('translations', fn ($query) => $query
                            ->where('title', 'like', "%{$keyword}%")
                            ->orWhere('subtitle', 'like', "%{$keyword}%"));
                });
            })
            ->when($filters['position'] ?? null, fn ($query, $position) => $query->where('position', $position))
            ->when(isset($filters['status']) && $filters['status'] !== '', fn ($query) => $query->where('status', (bool) $filters['status']))
            ->when($filters['schedule'] ?? null, function ($query, string $schedule): void {
                match ($schedule) {
                    'active_now' => $query->visible(),
                    'scheduled' => $query->where('starts_at', '>', now()),
                    'expired' => $query->whereNotNull('ends_at')->where('ends_at', '<', now()),
                    default => null,
                };
            })
            ->orderBy('position')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Banner
    {
        return $this->persist(new Banner, $data);
    }

    public function update(Banner $banner, array $data): Banner
    {
        return $this->persist($banner, $data);
    }

    public function delete(Banner $banner): void
    {
        $paths = array_filter([$banner->image_path, $banner->mobile_image_path]);
        $banner->delete();
        Storage::disk('public')->delete($paths);
        $this->clearCache();
    }

    public function translation(Banner $banner, Language|string|null $language = null, ?Language $defaultLanguage = null): ?BannerTranslation
    {
        $code = $language instanceof Language ? $language->code : $language;
        $defaultCode = $defaultLanguage?->code ?? app(LanguageService::class)->getDefault()?->code;
        $translations = $banner->relationLoaded('translations') ? $banner->translations : $banner->translations()->get();

        return $translations->firstWhere('language_code', $code)
            ?? $translations->firstWhere('language_code', $defaultCode)
            ?? $translations->first();
    }

    public function title(Banner $banner, ?string $languageCode = null): string
    {
        return $this->translation($banner, $languageCode)?->title ?? '—';
    }

    public function forPosition(string $position, Language $language, Language $defaultLanguage): Collection
    {
        if (! in_array($position, self::POSITIONS, true)) {
            return collect();
        }

        return Banner::query()
            ->visible()
            ->where('position', $position)
            ->with(['translations' => fn ($query) => $query->whereIn('language_code', array_unique([$language->code, $defaultLanguage->code]))])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Banner $banner) use ($language, $defaultLanguage): Banner {
                $banner->setRelation('displayTranslation', $this->translation($banner, $language, $defaultLanguage));

                return $banner;
            })
            ->filter(fn (Banner $banner): bool => $banner->image_path !== null || $banner->getRelation('displayTranslation') !== null)
            ->values();
    }

    public function imageUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function scheduleStatus(Banner $banner): string
    {
        return match (true) {
            ! $banner->status => 'inactive',
            $banner->starts_at?->isFuture() => 'scheduled',
            $banner->ends_at?->isPast() => 'expired',
            default => 'active_now',
        };
    }

    public function clearCache(): void
    {
        // Public banner queries are intentionally uncached so schedule boundaries apply immediately.
    }

    private function persist(Banner $banner, array $data): Banner
    {
        $newPaths = [];
        $oldPaths = [];

        try {
            if (($data['image'] ?? null) instanceof UploadedFile) {
                $newPaths['image_path'] = $data['image']->store('banners', 'public');
                $oldPaths[] = $banner->image_path;
            } elseif ($data['remove_image'] ?? false) {
                $newPaths['image_path'] = null;
                $oldPaths[] = $banner->image_path;
            }

            if (($data['mobile_image'] ?? null) instanceof UploadedFile) {
                $newPaths['mobile_image_path'] = $data['mobile_image']->store('banners', 'public');
                $oldPaths[] = $banner->mobile_image_path;
            } elseif ($data['remove_mobile_image'] ?? false) {
                $newPaths['mobile_image_path'] = null;
                $oldPaths[] = $banner->mobile_image_path;
            }

            DB::transaction(function () use ($banner, $data, $newPaths): void {
                $banner->fill([
                    'position' => $data['position'],
                    'link_url' => $data['link_url'] ?? null,
                    'link_target' => $data['link_target'],
                    'sort_order' => $data['sort_order'],
                    'status' => $data['status'],
                    'starts_at' => $data['starts_at'] ?? null,
                    'ends_at' => $data['ends_at'] ?? null,
                    ...$newPaths,
                ])->save();

                $this->syncTranslations($banner, $data['translations']);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete(array_filter($newPaths));
            throw $exception;
        }

        Storage::disk('public')->delete(array_filter($oldPaths));
        $this->clearCache();

        return $banner->refresh()->load('translations');
    }

    private function syncTranslations(Banner $banner, array $translations): void
    {
        foreach ($translations as $languageCode => $translation) {
            if (collect($translation)->every(fn ($value): bool => blank($value))) {
                $banner->translations()->where('language_code', $languageCode)->delete();

                continue;
            }

            $banner->translations()->updateOrCreate(
                ['language_code' => $languageCode],
                $translation,
            );
        }
    }
}
