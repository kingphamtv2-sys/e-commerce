<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTaxRateRequest;
use App\Http\Requests\Admin\UpdateTaxRateRequest;
use App\Models\TaxClass;
use App\Models\TaxRate;
use App\Services\TaxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaxRateController extends Controller
{
    public function index(TaxService $taxService): View
    {
        return view('admin.tax-rates.index', ['taxRates' => $taxService->rates()]);
    }

    public function create(): View
    {
        return view('admin.tax-rates.create', [
            'taxRate' => new TaxRate(['priority' => 1, 'status' => true]),
            'taxClasses' => TaxClass::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTaxRateRequest $request, TaxService $taxService): RedirectResponse
    {
        TaxRate::query()->create($request->validated());
        $taxService->clearCache();

        return redirect()->route('admin.tax-rates.index')->with('success', __('admin.messages.tax_rate_created'));
    }

    public function edit(TaxRate $taxRate): View
    {
        return view('admin.tax-rates.edit', [
            'taxRate' => $taxRate,
            'taxClasses' => TaxClass::query()
                ->where('status', true)
                ->orWhereKey($taxRate->tax_class_id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(UpdateTaxRateRequest $request, TaxRate $taxRate, TaxService $taxService): RedirectResponse
    {
        $taxRate->update($request->validated());
        $taxService->clearCache();

        return redirect()->route('admin.tax-rates.index')->with('success', __('admin.messages.tax_rate_updated'));
    }

    public function destroy(TaxRate $taxRate, TaxService $taxService): RedirectResponse
    {
        $taxRate->delete();
        $taxService->clearCache();

        return redirect()->route('admin.tax-rates.index')->with('success', __('admin.messages.tax_rate_deleted'));
    }
}
