<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\TaxClass;
use App\Services\CategoryService;
use App\Services\CurrencyService;
use App\Services\LanguageService;
use App\Services\ProductImageService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(
        Request $request,
        ProductService $productService,
        CategoryService $categoryService,
        CurrencyService $currencyService,
    ): View {
        $filters = $request->validate([
            'keyword' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:0,1'],
            'is_featured' => ['nullable', 'in:0,1'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc'],
        ]);

        return view('admin.products.index', [
            'products' => $productService->paginate($filters),
            'categories' => collect($categoryService->all()),
            'productService' => $productService,
            'categoryService' => $categoryService,
            'currencyService' => $currencyService,
            'defaultCurrency' => $currencyService->getDefault(),
            'filters' => $filters,
        ]);
    }

    public function create(
        LanguageService $languageService,
        CategoryService $categoryService,
        CurrencyService $currencyService,
    ): View {
        return view('admin.products.create', [
            ...$this->formData(new Product(['price' => 0, 'status' => true, 'is_featured' => false]), $languageService, $categoryService, $currencyService),
            'translations' => collect(),
            'variants' => collect(),
        ]);
    }

    public function store(StoreProductRequest $request, ProductService $productService): RedirectResponse
    {
        $productService->create($request->validated());

        return redirect()->route('admin.products.index')->with('success', __('admin.messages.product_created'));
    }

    public function edit(
        Product $product,
        LanguageService $languageService,
        CategoryService $categoryService,
        CurrencyService $currencyService,
        ProductImageService $productImageService,
    ): View {
        return view('admin.products.edit', [
            ...$this->formData($product, $languageService, $categoryService, $currencyService),
            'translations' => $product->productTranslations()->get()->keyBy('language_code'),
            'variants' => $product->productVariants()->orderBy('id')->get(),
            'productImages' => $productImageService->images($product),
            'productImageService' => $productImageService,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product, ProductService $productService): RedirectResponse
    {
        $productService->update($product, $request->validated());

        return redirect()->route('admin.products.index')->with('success', __('admin.messages.product_updated'));
    }

    public function destroy(Product $product, ProductService $productService): RedirectResponse
    {
        if ($product->orderItems()->exists()) {
            return back()->withErrors(['product' => __('admin.messages.product_has_orders')]);
        }

        $productService->delete($product);

        return redirect()->route('admin.products.index')->with('success', __('admin.messages.product_deleted'));
    }

    private function formData(
        Product $product,
        LanguageService $languageService,
        CategoryService $categoryService,
        CurrencyService $currencyService,
    ): array {
        $categories = Category::query()
            ->where(fn ($query) => $query->active()->when($product->category_id, fn ($query) => $query->orWhere('id', $product->category_id)))
            ->with('categoryTranslations')
            ->orderBy('sort_order')
            ->get();
        $taxClasses = TaxClass::query()
            ->where(fn ($query) => $query->active()->when($product->tax_class_id, fn ($query) => $query->orWhere('id', $product->tax_class_id)))
            ->orderBy('name')
            ->get();

        return [
            'product' => $product,
            'languages' => $languageService->active(),
            'defaultLanguage' => $languageService->getDefault(),
            'categories' => $categories,
            'taxClasses' => $taxClasses,
            'categoryService' => $categoryService,
            'defaultCurrency' => $currencyService->getDefault(),
        ];
    }
}
