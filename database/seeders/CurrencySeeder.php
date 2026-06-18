<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::query()->upsert([
            ['code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫', 'exchange_rate' => 1, 'decimal_places' => 0, 'symbol_position' => 'after', 'thousand_separator' => ',', 'decimal_separator' => '.', 'is_default' => true, 'status' => true],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate' => 25000, 'decimal_places' => 2, 'symbol_position' => 'before', 'thousand_separator' => ',', 'decimal_separator' => '.', 'is_default' => false, 'status' => true],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'exchange_rate' => 170, 'decimal_places' => 0, 'symbol_position' => 'before', 'thousand_separator' => ',', 'decimal_separator' => '.', 'is_default' => false, 'status' => true],
        ], ['code'], ['name', 'symbol', 'exchange_rate', 'decimal_places', 'symbol_position', 'thousand_separator', 'decimal_separator', 'is_default', 'status']);
    }
}
