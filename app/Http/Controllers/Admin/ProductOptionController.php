<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductOptionRequest;
use App\Models\Product;
use App\Models\ProductOption;
use App\Services\ProductVariantService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProductOptionController extends Controller
{
    public function store(ProductOptionRequest $request, Product $product, ProductVariantService $service): JsonResponse|RedirectResponse
    {
        $option = $service->createOption($product, $request->validated());

        if ($request->expectsJson()) {
            $option->load('values');

            return response()->json([
                'success' => true,
                'message' => __('admin.messages.option_created'),
                'option' => $option,
                'html' => view('admin.products.partials.option-card', compact('option'))->render(),
                'variant_selector_html' => $option->status
                    ? view('admin.products.partials.variant-selector', compact('option'))->render()
                    : null,
            ]);
        }

        return back()->with('success', __('admin.messages.option_created'));
    }

    public function update(ProductOptionRequest $request, ProductOption $productOption, ProductVariantService $service): JsonResponse|RedirectResponse
    {
        $option = $service->updateOption($productOption, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['message' => __('admin.messages.option_updated'), 'option' => $option]);
        }

        return back()->with('success', __('admin.messages.option_updated'));
    }

    public function destroy(ProductOption $productOption, ProductVariantService $service): JsonResponse|RedirectResponse
    {
        $optionId = $productOption->id;

        try {
            $service->deleteOption($productOption);
        } catch (DomainException $exception) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'errors' => ['option' => [$exception->getMessage()]],
                ], 422);
            }

            return back()->withErrors(['option' => $exception->getMessage()]);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('admin.messages.option_deleted'),
                'option_id' => $optionId,
            ]);
        }

        return back()->with('success', __('admin.messages.option_deleted'));
    }
}
