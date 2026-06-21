<?php

namespace Database\Seeders;

use App\Models\TaxClass;
use App\Services\TaxService;
use Illuminate\Database\Seeder;

class TaxClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['code' => 'standard_tax', 'name' => 'Standard Tax', 'description' => 'Thuế tiêu chuẩn', 'status' => true],
            ['code' => 'reduced_tax', 'name' => 'Reduced Tax', 'description' => 'Thuế giảm', 'status' => true],
            ['code' => 'tax_free', 'name' => 'Tax Free', 'description' => 'Không tính thuế', 'status' => true],
        ] as $taxClass) {
            TaxClass::query()->updateOrCreate(['code' => $taxClass['code']], $taxClass);
        }

        app(TaxService::class)->clearCache();
    }
}
