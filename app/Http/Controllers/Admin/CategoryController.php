<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use App\Services\LanguageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request, CategoryService $categoryService): View
    {
        $filters = $request->validate([
            'keyword' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:0,1'],
            'parent_id' => ['nullable', 'integer'],
        ]);

        return view('admin.categories.index', [
            'categories' => $categoryService->paginate($filters),
            'parentCategories' => $categoryService->parentOptions(),
            'categoryService' => $categoryService,
            'filters' => $filters,
        ]);
    }

    public function create(LanguageService $languageService, CategoryService $categoryService): View
    {
        return view('admin.categories.create', [
            'category' => new Category(['sort_order' => 0, 'status' => true]),
            'languages' => $languageService->active(),
            'defaultLanguage' => $languageService->getDefault(),
            'parentCategories' => $categoryService->parentOptions(),
            'translations' => collect(),
            'categoryService' => $categoryService,
        ]);
    }

    public function store(StoreCategoryRequest $request, CategoryService $categoryService): RedirectResponse
    {
        $categoryService->create($request->validated());

        return redirect()->route('admin.categories.index')->with('success', __('admin.messages.category_created'));
    }

    public function edit(Category $category, LanguageService $languageService, CategoryService $categoryService): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'languages' => $languageService->active(),
            'defaultLanguage' => $languageService->getDefault(),
            'parentCategories' => $categoryService->parentOptions($category),
            'translations' => $category->categoryTranslations()->get()->keyBy('language_code'),
            'categoryService' => $categoryService,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category, CategoryService $categoryService): RedirectResponse
    {
        $categoryService->update($category, $request->validated());

        return redirect()->route('admin.categories.index')->with('success', __('admin.messages.category_updated'));
    }

    public function destroy(Category $category, CategoryService $categoryService): RedirectResponse
    {
        if ($category->children()->exists()) {
            return back()->withErrors(['category' => __('admin.messages.category_has_children')]);
        }

        if ($category->products()->exists()) {
            return back()->withErrors(['category' => __('admin.messages.category_has_products')]);
        }

        $categoryService->delete($category);

        return redirect()->route('admin.categories.index')->with('success', __('admin.messages.category_deleted'));
    }
}
