<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Language::query()->upsert([
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'is_default' => true, 'status' => true, 'sort_order' => 1],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_default' => false, 'status' => true, 'sort_order' => 2],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'is_default' => false, 'status' => true, 'sort_order' => 3],
        ], ['code'], ['name', 'native_name', 'is_default', 'status', 'sort_order']);
    }
}
