<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductVariantCombinationRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductVariantService;
use App\Services\VariantImageService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProductVariantController extends Controller
{
    public function store(ProductVariantCombinationRequest $request, Product $product, ProductVariantService $service, VariantImageService $variantImageService): JsonResponse|RedirectResponse
    {
        try {
            $variant = $service->createVariant($product, $request->validated(), $request->user());
        } catch (DomainException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage(), 'errors' => ['variant' => [$exception->getMessage()]]], 422);
            }

            return back()->withInput()->withErrors(['variant' => $exception->getMessage()]);
        }

        if ($request->expectsJson()) {
            $variant->load(['optionValues.option', 'inventoryStock', 'variantImages']);
            $stock = $variant->inventoryStock?->load(['product', 'productVariant']);

            return response()->json([
                'success' => true,
                'message' => __('admin.messages.variant_created'),
                'variant' => $variant,
                'html' => view('admin.products.partials.variant-row', [
                    'variant' => $variant,
                    'activeOptions' => $product->productOptions()->active()->with(['values' => fn ($query) => $query->active()])->get(),
                ])->render(),
                'variant_image_panel_html' => view('admin.products.partials.variant-image-panel', compact('variant', 'variantImageService'))->render(),
                'inventory_html' => $stock ? view('admin.products.partials.inventory-row', compact('stock'))->render() : null,
                'product_stock_id' => $product->inventoryStocks()->whereNull('product_variant_id')->value('id'),
            ]);
        }

        return back()->with('success', __('admin.messages.variant_created'));
    }

    public function update(ProductVariantCombinationRequest $request, ProductVariant $productVariant, ProductVariantService $service): JsonResponse|RedirectResponse
    {
        try {
            $variant = $service->updateVariant($productVariant, $request->validated());
        } catch (DomainException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage(), 'errors' => ['variant' => [$exception->getMessage()]]], 422);
            }

            return back()->withInput()->withErrors(['variant' => $exception->getMessage()]);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => __('admin.messages.variant_updated'), 'variant' => $variant]);
        }

        return back()->with('success', __('admin.messages.variant_updated'));
    }

    public function destroy(ProductVariant $productVariant, ProductVariantService $service): JsonResponse|RedirectResponse
    {
        $variantId = $productVariant->id;
        $inventoryStockId = $productVariant->inventoryStock()->value('id');

        try {
            $service->deleteVariant($productVariant);
        } catch (DomainException $exception) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'errors' => ['variant' => [$exception->getMessage()]],
                ], 422);
            }

            return back()->withErrors(['variant' => $exception->getMessage()]);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('admin.messages.variant_deleted'),
                'variant_id' => $variantId,
                'inventory_stock_id' => $inventoryStockId,
            ]);
        }

        return back()->with('success', __('admin.messages.variant_deleted'));
    }
}
