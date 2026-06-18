<?php

namespace App\Services;

use App\Models\Language;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LanguageService
{
    public const CACHE_ALL = 'languages_all';

    public const CACHE_ACTIVE = 'languages_active';

    public const CACHE_DEFAULT = 'language_default';

    /** @return array<int, Language> */
    public function all(): array
    {
        return Cache::rememberForever(
            self::CACHE_ALL,
            static fn (): array => Language::query()->orderBy('sort_order')->orderBy('name')->get()->all(),
        );
    }

    /** @return array<int, Language> */
    public function active(): array
    {
        return Cache::rememberForever(
            self::CACHE_ACTIVE,
            static fn (): array => Language::query()->active()->orderBy('sort_order')->orderBy('name')->get()->all(),
        );
    }

    public function getDefault(): ?Language
    {
        return Cache::rememberForever(
            self::CACHE_DEFAULT,
            static fn (): ?Language => Language::query()->default()->first(),
        );
    }

    public function findByCode(string $code): ?Language
    {
        return collect($this->all())->firstWhere('code', strtolower($code));
    }

    public function setDefault(Language $language): void
    {
        if (! $language->status) {
            throw new DomainException(__('admin.messages.inactive_cannot_default'));
        }

        DB::transaction(function () use ($language): void {
            Language::query()->where('id', '!=', $language->getKey())->update(['is_default' => false]);
            $language->forceFill(['is_default' => true])->save();
        });

        $this->clearCache();
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_ALL);
        Cache::forget(self::CACHE_ACTIVE);
        Cache::forget(self::CACHE_DEFAULT);
    }
}
