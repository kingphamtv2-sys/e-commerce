<?php

namespace Database\Seeders;

use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Services\TaxService;
use Illuminate\Database\Seeder;

class TaxRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxClasses = TaxClass::query()->pluck('id', 'code');

        foreach (['standard_tax' => 10, 'reduced_tax' => 5, 'tax_free' => 0] as $code => $rate) {
            TaxRate::query()->updateOrCreate(
                ['tax_class_id' => $taxClasses[$code], 'country_code' => 'VN', 'region' => null],
                ['rate' => $rate, 'priority' => 1, 'status' => true],
            );
        }

        app(TaxService::class)->clearCache();
    }
}
