<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLanguageRequest;
use App\Http\Requests\Admin\UpdateLanguageRequest;
use App\Models\Language;
use App\Services\LanguageService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function index(LanguageService $languageService): View
    {
        return view('admin.languages.index', [
            'languages' => $languageService->all(),
        ]);
    }

    public function create(): View
    {
        return view('admin.languages.create', [
            'language' => new Language(['status' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(StoreLanguageRequest $request, LanguageService $languageService): RedirectResponse
    {
        $data = $request->validated();
        $makeDefault = $data['is_default'] || ! Language::query()->default()->exists();

        if ($makeDefault && ! $data['status']) {
            throw ValidationException::withMessages(['status' => 'Default language must be active.']);
        }

        DB::transaction(function () use ($data, $makeDefault, $languageService): void {
            $language = Language::query()->create([
                ...Arr::except($data, 'is_default'),
                'is_default' => false,
            ]);

            if ($makeDefault) {
                $languageService->setDefault($language);
            }
        });

        $languageService->clearCache();

        return redirect()->route('admin.languages.index')->with('success', __('admin.messages.language_created'));
    }

    public function edit(Language $language): View
    {
        return view('admin.languages.edit', compact('language'));
    }

    public function update(UpdateLanguageRequest $request, Language $language, LanguageService $languageService): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $language, $languageService): void {
            $language->update(Arr::except($data, 'is_default'));

            if ($data['is_default']) {
                $languageService->setDefault($language->refresh());
            }
        });

        $languageService->clearCache();

        return redirect()->route('admin.languages.index')->with('success', __('admin.messages.language_updated'));
    }

    public function destroy(Language $language, LanguageService $languageService): RedirectResponse
    {
        if ($language->is_default) {
            return back()->withErrors(['language' => __('admin.messages.cannot_delete_default')]);
        }

        $language->delete();
        $languageService->clearCache();

        return redirect()->route('admin.languages.index')->with('success', __('admin.messages.language_deleted'));
    }

    public function setDefault(Language $language, LanguageService $languageService): RedirectResponse
    {
        try {
            $languageService->setDefault($language);
        } catch (DomainException $exception) {
            return back()->withErrors(['language' => $exception->getMessage()]);
        }

        app()->setLocale($language->code);

        return back()->with('success', __('admin.messages.default_updated'));
    }
}
