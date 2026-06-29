<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Services\LanguageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'is_default' => true, 'status' => true, 'sort_order' => 1],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_default' => false, 'status' => true, 'sort_order' => 2],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'is_default' => false, 'status' => true, 'sort_order' => 3],
        ];

        DB::transaction(function () use ($languages): void {
            Language::query()->update(['is_default' => false]);

            foreach ($languages as $language) {
                Language::query()->updateOrCreate(['code' => $language['code']], $language);
            }
        });

        app(LanguageService::class)->clearCache();
    }
}
