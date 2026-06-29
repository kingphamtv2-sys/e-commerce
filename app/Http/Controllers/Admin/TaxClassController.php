<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTaxClassRequest;
use App\Http\Requests\Admin\UpdateTaxClassRequest;
use App\Models\TaxClass;
use App\Services\TaxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaxClassController extends Controller
{
    public function index(TaxService $taxService): View
    {
        return view('admin.tax-classes.index', ['taxClasses' => $taxService->classes()]);
    }

    public function create(): View
    {
        return view('admin.tax-classes.create', ['taxClass' => new TaxClass(['status' => true])]);
    }

    public function store(StoreTaxClassRequest $request, TaxService $taxService): RedirectResponse
    {
        TaxClass::query()->create($request->validated());
        $taxService->clearCache();

        return redirect()->route('admin.tax-classes.index')->with('success', __('admin.messages.tax_class_created'));
    }

    public function edit(TaxClass $taxClass): View
    {
        return view('admin.tax-classes.edit', compact('taxClass'));
    }

    public function update(UpdateTaxClassRequest $request, TaxClass $taxClass, TaxService $taxService): RedirectResponse
    {
        $taxClass->update($request->validated());
        $taxService->clearCache();

        return redirect()->route('admin.tax-classes.index')->with('success', __('admin.messages.tax_class_updated'));
    }

    public function destroy(TaxClass $taxClass, TaxService $taxService): RedirectResponse
    {
        if ($taxClass->taxRates()->exists()) {
            return back()->withErrors(['tax_class' => __('admin.messages.tax_class_in_use')]);
        }

        $taxClass->delete();
        $taxService->clearCache();

        return redirect()->route('admin.tax-classes.index')->with('success', __('admin.messages.tax_class_deleted'));
    }
}
