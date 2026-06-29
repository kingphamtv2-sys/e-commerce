<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryService
{
    public const CACHE_ALL = 'categories_all';

    /** @return array<int, Category> */
    public function all(): array
    {
        return Cache::rememberForever(
            self::CACHE_ALL,
            static fn (): array => Category::query()
                ->with(['categoryTranslations', 'parent.categoryTranslations'])
                ->withCount(['children', 'products'])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->all(),
        );
    }

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $categories = collect($this->all());
        $keyword = Str::lower(trim((string) ($filters['keyword'] ?? '')));

        if ($keyword !== '') {
            $categories = $categories->filter(function (Category $category) use ($keyword): bool {
                return $category->categoryTranslations->contains(
                    fn (CategoryTranslation $translation): bool => Str::contains(Str::lower($translation->name), $keyword),
                );
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $categories = $categories->where('status', (bool) $filters['status']);
        }

        if (! empty($filters['parent_id'])) {
            $categories = $categories->where('parent_id', (int) $filters['parent_id']);
        }

        $page = Paginator::resolveCurrentPage();

        return new Paginator(
            $categories->forPage($page, $perPage)->values(),
            $categories->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => request()->query()],
        );
    }

    public function translation(Category $category, ?string $languageCode = null): ?CategoryTranslation
    {
        $translations = $category->relationLoaded('categoryTranslations')
            ? $category->categoryTranslations
            : $category->categoryTranslations()->get();
        $defaultCode = app(LanguageService::class)->getDefault()?->code;

        return $translations->firstWhere('language_code', $languageCode ?: $defaultCode)
            ?? $translations->firstWhere('language_code', $defaultCode)
            ?? $translations->first();
    }

    public function name(?Category $category, ?string $languageCode = null): string
    {
        return $category ? ($this->translation($category, $languageCode)?->name ?? '—') : '—';
    }

    /** @return Collection<int, Category> */
    public function parentOptions(?Category $category = null): Collection
    {
        $excludedIds = $category ? [$category->id, ...$this->descendantIds($category)] : [];

        return collect($this->all())->reject(fn (Category $option): bool => in_array($option->id, $excludedIds, true));
    }

    public function create(array $data): Category
    {
        $category = DB::transaction(function () use ($data): Category {
            $category = Category::query()->create($this->generalData($data));
            $this->syncTranslations($category, $data['translations']);

            return $category;
        });

        $this->clearCache();

        return $category;
    }

    public function update(Category $category, array $data): Category
    {
        DB::transaction(function () use ($category, $data): void {
            $category->update($this->generalData($data));
            $this->syncTranslations($category, $data['translations']);
        });

        $this->clearCache();

        return $category->refresh();
    }

    public function delete(Category $category): void
    {
        DB::transaction(function () use ($category): void {
            $category->categoryTranslations()->delete();
            $category->delete();
        });

        $this->clearCache();
    }

    /** @return array<int, int> */
    public function descendantIds(Category $category): array
    {
        $descendants = [];
        $pending = [$category->id];

        while ($pending !== []) {
            $children = Category::query()->whereIn('parent_id', $pending)->pluck('id')->all();
            $newIds = array_values(array_diff($children, $descendants));
            $descendants = [...$descendants, ...$newIds];
            $pending = $newIds;
        }

        return $descendants;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_ALL);
    }

    private function generalData(array $data): array
    {
        return [
            'parent_id' => $data['parent_id'] ?? null,
            'image' => $data['image'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'status' => $data['status'],
        ];
    }

    private function syncTranslations(Category $category, array $translations): void
    {
        foreach ($translations as $languageCode => $translation) {
            if (blank($translation['name'] ?? null)) {
                $category->categoryTranslations()->where('language_code', $languageCode)->delete();

                continue;
            }

            $category->categoryTranslations()->updateOrCreate(
                ['language_code' => $languageCode],
                [
                    'name' => $translation['name'],
                    'slug' => $translation['slug'],
                    'description' => $translation['description'] ?? null,
                    'meta_title' => $translation['meta_title'] ?? null,
                    'meta_description' => $translation['meta_description'] ?? null,
                ],
            );
        }
    }
}
