<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\CategoryService;
use App\Services\CouponService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $coupons = Coupon::query()
            ->withCount(['categories', 'products', 'usages'])
            ->when($request->filled('keyword'), fn ($query) => $query->where(function ($query) use ($request): void {
                $keyword = trim((string) $request->input('keyword'));
                $query->where('code', 'like', "%{$keyword}%")->orWhere('name', 'like', "%{$keyword}%");
            }))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('discount_type'), fn ($query) => $query->where('discount_type', $request->input('discount_type')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.coupons.index', ['coupons' => $coupons, 'filters' => $request->only(['keyword', 'status', 'discount_type'])]);
    }

    public function create(CategoryService $categoryService, ProductService $productService): View
    {
        return view('admin.coupons.create', [
            'coupon' => new Coupon(['discount_type' => Coupon::TYPE_PERCENTAGE, 'status' => Coupon::STATUS_ACTIVE]),
            'categories' => Category::query()->with('categoryTranslations')->orderBy('sort_order')->orderBy('id')->get(),
            'products' => Product::query()->with('productTranslations')->latest()->get(),
            'categoryService' => $categoryService,
            'productService' => $productService,
        ]);
    }

    public function store(CouponRequest $request, CouponService $service): RedirectResponse
    {
        $coupon = $service->create($request->validated());

        return redirect()->route('admin.coupons.edit', $coupon)->with('success', __('admin.coupons.created'));
    }

    public function edit(Coupon $coupon, CategoryService $categoryService, ProductService $productService): View
    {
        $coupon->load(['categories', 'products']);

        return view('admin.coupons.edit', [
            'coupon' => $coupon,
            'categories' => Category::query()->with('categoryTranslations')->orderBy('sort_order')->orderBy('id')->get(),
            'products' => Product::query()->with('productTranslations')->latest()->get(),
            'categoryService' => $categoryService,
            'productService' => $productService,
        ]);
    }

    public function update(CouponRequest $request, Coupon $coupon, CouponService $service): RedirectResponse
    {
        $service->update($coupon, $request->validated());

        return back()->with('success', __('admin.coupons.updated'));
    }

    public function destroy(Coupon $coupon, CouponService $service): RedirectResponse
    {
        return redirect()->route('admin.coupons.index')->with('success', $service->deleteOrDisable($coupon));
    }
}
