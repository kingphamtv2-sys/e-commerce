<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCurrencyRequest;
use App\Http\Requests\Admin\UpdateCurrencyRequest;
use App\Models\Currency;
use App\Services\CurrencyService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    public function index(CurrencyService $currencyService): View
    {
        return view('admin.currencies.index', [
            'currencies' => $currencyService->all(),
            'currencyService' => $currencyService,
        ]);
    }

    public function create(): View
    {
        return view('admin.currencies.create', [
            'currency' => new Currency([
                'exchange_rate' => 1,
                'decimal_places' => 0,
                'symbol_position' => 'after',
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'status' => true,
            ]),
        ]);
    }

    public function store(StoreCurrencyRequest $request, CurrencyService $currencyService): RedirectResponse
    {
        $data = $request->validated();
        $makeDefault = $data['is_default'] || ! Currency::query()->default()->exists();

        if ($makeDefault && ! $data['status']) {
            throw ValidationException::withMessages(['status' => 'Default currency must be active.']);
        }

        DB::transaction(function () use ($data, $makeDefault, $currencyService): void {
            $currency = Currency::query()->create([
                ...Arr::except($data, 'is_default'),
                'is_default' => false,
            ]);

            if ($makeDefault) {
                $currencyService->setDefault($currency);
            }
        });

        $currencyService->clearCache();

        return redirect()->route('admin.currencies.index')->with('success', __('admin.messages.currency_created'));
    }

    public function edit(Currency $currency): View
    {
        return view('admin.currencies.edit', compact('currency'));
    }

    public function update(UpdateCurrencyRequest $request, Currency $currency, CurrencyService $currencyService): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $currency, $currencyService): void {
            $currency->update(Arr::except($data, 'is_default'));

            if ($data['is_default']) {
                $currencyService->setDefault($currency->refresh());
            }
        });

        $currencyService->clearCache();

        return redirect()->route('admin.currencies.index')->with('success', __('admin.messages.currency_updated'));
    }

    public function destroy(Currency $currency, CurrencyService $currencyService): RedirectResponse
    {
        if ($currency->is_default) {
            return back()->withErrors(['currency' => __('admin.messages.cannot_delete_default_currency')]);
        }

        $currency->delete();
        $currencyService->clearCache();

        return redirect()->route('admin.currencies.index')->with('success', __('admin.messages.currency_deleted'));
    }

    public function setDefault(Currency $currency, CurrencyService $currencyService): RedirectResponse
    {
        try {
            $currencyService->setDefault($currency);
        } catch (DomainException $exception) {
            return back()->withErrors(['currency' => $exception->getMessage()]);
        }

        return back()->with('success', __('admin.messages.default_currency_updated'));
    }
}
