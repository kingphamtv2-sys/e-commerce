<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            ['code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫', 'exchange_rate' => 1, 'decimal_places' => 0, 'symbol_position' => 'after', 'thousand_separator' => ',', 'decimal_separator' => '.', 'is_default' => true, 'status' => true],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate' => 25000, 'decimal_places' => 2, 'symbol_position' => 'before', 'thousand_separator' => ',', 'decimal_separator' => '.', 'is_default' => false, 'status' => true],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'exchange_rate' => 170, 'decimal_places' => 0, 'symbol_position' => 'before', 'thousand_separator' => ',', 'decimal_separator' => '.', 'is_default' => false, 'status' => true],
        ];

        DB::transaction(function () use ($currencies): void {
            Currency::query()->update(['is_default' => false]);

            foreach ($currencies as $currency) {
                Currency::query()->updateOrCreate(['code' => $currency['code']], $currency);
            }
        });

        app(CurrencyService::class)->clearCache();
    }
}
