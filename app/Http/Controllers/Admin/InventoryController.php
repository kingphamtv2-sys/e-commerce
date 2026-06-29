<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustInventoryRequest;
use App\Models\Category;
use App\Models\InventoryStock;
use App\Services\CategoryService;
use App\Services\InventoryService;
use App\Services\ProductService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(
        Request $request,
        InventoryService $inventoryService,
        CategoryService $categoryService,
        ProductService $productService,
    ): View {
        $filters = $request->validate([
            'keyword' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer'],
            'stock_status' => ['nullable', 'in:in_stock,low_stock,out_of_stock'],
            'product_type' => ['nullable', 'in:product,variant'],
        ]);
        $inventoryService->syncStockRecords($request->user());

        return view('admin.inventory.index', [
            'stocks' => $inventoryService->paginate($filters),
            'categories' => Category::query()->with('categoryTranslations')->orderBy('sort_order')->get(),
            'inventoryService' => $inventoryService,
            'categoryService' => $categoryService,
            'productService' => $productService,
            'filters' => $filters,
        ]);
    }

    public function show(
        InventoryStock $inventoryStock,
        InventoryService $inventoryService,
        CategoryService $categoryService,
        ProductService $productService,
    ): View {
        $this->loadStock($inventoryStock);

        return view('admin.inventory.show', [
            'stock' => $inventoryStock,
            'logs' => $inventoryService->logs($inventoryStock, 10),
            'inventoryService' => $inventoryService,
            'categoryService' => $categoryService,
            'productService' => $productService,
        ]);
    }

    public function adjust(
        InventoryStock $inventoryStock,
        InventoryService $inventoryService,
        ProductService $productService,
    ): View {
        $this->loadStock($inventoryStock);

        return view('admin.inventory.adjust', [
            'stock' => $inventoryStock,
            'inventoryService' => $inventoryService,
            'productService' => $productService,
        ]);
    }

    public function update(
        AdjustInventoryRequest $request,
        InventoryStock $inventoryStock,
        InventoryService $inventoryService,
    ): JsonResponse|RedirectResponse {
        try {
            $stock = $inventoryService->adjust($inventoryStock, $request->validated(), $request->user());
        } catch (DomainException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $exception->getMessage(), 'errors' => ['quantity' => [$exception->getMessage()]]], 422);
            }

            return back()->withInput()->withErrors(['quantity' => $exception->getMessage()]);
        }

        if ($request->expectsJson()) {
            $stock->load(['product', 'productVariant']);
            $log = $stock->inventoryLogs()->with('createdBy')->latest()->first();

            return response()->json([
                'success' => true,
                'message' => __('admin.messages.inventory_adjusted'),
                'html' => view('admin.products.partials.inventory-row', compact('stock'))->render(),
                'log_html' => $log ? view('admin.products.partials.inventory-log', compact('log'))->render() : null,
            ]);
        }

        return redirect()->route('admin.inventory.show', $inventoryStock)->with('success', __('admin.messages.inventory_adjusted'));
    }

    public function logs(
        InventoryStock $inventoryStock,
        InventoryService $inventoryService,
        ProductService $productService,
    ): View {
        $this->loadStock($inventoryStock);

        return view('admin.inventory.logs', [
            'stock' => $inventoryStock,
            'logs' => $inventoryService->logs($inventoryStock),
            'inventoryService' => $inventoryService,
            'productService' => $productService,
        ]);
    }

    private function loadStock(InventoryStock $stock): void
    {
        $stock->load(['product.productTranslations', 'product.category.categoryTranslations', 'productVariant']);
        abort_if($stock->product === null, 404);
    }
}
