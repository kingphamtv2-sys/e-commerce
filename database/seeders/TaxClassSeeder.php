<?php

namespace Database\Seeders;

use App\Models\TaxClass;
use Illuminate\Database\Seeder;

class TaxClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TaxClass::query()->upsert([
            ['code' => 'standard_tax', 'name' => 'Standard Tax', 'description' => 'Standard tax class', 'status' => true],
            ['code' => 'reduced_tax', 'name' => 'Reduced Tax', 'description' => 'Reduced tax class', 'status' => true],
            ['code' => 'tax_free', 'name' => 'Tax Free', 'description' => 'Tax-exempt class', 'status' => true],
        ], ['code'], ['name', 'description', 'status']);
    }
}
