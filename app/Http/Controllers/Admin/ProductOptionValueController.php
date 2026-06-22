<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductOptionValueRequest;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Services\ProductVariantService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProductOptionValueController extends Controller
{
    public function store(ProductOptionValueRequest $request, ProductOption $productOption, ProductVariantService $service): JsonResponse|RedirectResponse
    {
        $value = $service->createValue($productOption, $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('admin.messages.option_value_created'),
                'value' => $value,
                'html' => view('admin.products.partials.option-value-row', compact('value'))->render(),
            ]);
        }

        return back()->with('success', __('admin.messages.option_value_created'));
    }

    public function update(ProductOptionValueRequest $request, ProductOptionValue $productOptionValue, ProductVariantService $service): JsonResponse|RedirectResponse
    {
        $value = $service->updateValue($productOptionValue, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['message' => __('admin.messages.option_value_updated'), 'value' => $value]);
        }

        return back()->with('success', __('admin.messages.option_value_updated'));
    }

    public function destroy(ProductOptionValue $productOptionValue, ProductVariantService $service): JsonResponse|RedirectResponse
    {
        $valueId = $productOptionValue->id;
        $optionId = $productOptionValue->product_option_id;

        try {
            $service->deleteValue($productOptionValue);
        } catch (DomainException $exception) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'errors' => ['option_value' => [$exception->getMessage()]],
                ], 422);
            }

            return back()->withErrors(['option_value' => $exception->getMessage()]);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('admin.messages.option_value_deleted'),
                'option_value_id' => $valueId,
                'option_id' => $optionId,
            ]);
        }

        return back()->with('success', __('admin.messages.option_value_deleted'));
    }
}
