<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVariantImageRequest;
use App\Http\Requests\Admin\UpdateVariantImageRequest;
use App\Models\ProductVariant;
use App\Models\VariantImage;
use App\Services\ProductService;
use App\Services\VariantImageService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VariantImageController extends Controller
{
    public function index(ProductVariant $productVariant, VariantImageService $service, ProductService $productService): View
    {
        $productVariant->load(['product.productTranslations', 'optionValues.option']);

        return view('admin.variant-images.index', ['variant' => $productVariant, 'images' => $service->images($productVariant), 'service' => $service, 'productService' => $productService]);
    }

    public function store(StoreVariantImageRequest $request, ProductVariant $productVariant, VariantImageService $service): JsonResponse|RedirectResponse
    {
        $images = $service->upload($productVariant, $request->file('images'), $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('admin.messages.variant_images_uploaded'),
                'html' => $images->map(fn (VariantImage $image): string => view('admin.variant-images._card', ['image' => $image, 'service' => $service])->render())->implode(''),
                'main_image_id' => $productVariant->variantImages()->where('is_main', true)->value('id'),
                'variant_id' => $productVariant->id,
                'image_count' => $productVariant->variantImages()->count(),
            ]);
        }

        return back()->with('success', __('admin.messages.variant_images_uploaded'));
    }

    public function update(UpdateVariantImageRequest $request, VariantImage $variantImage, VariantImageService $service): RedirectResponse
    {
        try {
            $service->update($variantImage, $request->validated());
        } catch (DomainException $exception) {
            return back()->withErrors(['variant_image' => $exception->getMessage()]);
        }

        return back()->with('success', __('admin.messages.variant_image_updated'));
    }

    public function setMain(VariantImage $variantImage, VariantImageService $service): RedirectResponse
    {
        try {
            $service->setMain($variantImage);
        } catch (DomainException $exception) {
            return back()->withErrors(['variant_image' => $exception->getMessage()]);
        }

        return back()->with('success', __('admin.messages.variant_image_main_updated'));
    }

    public function destroy(VariantImage $variantImage, VariantImageService $service): RedirectResponse
    {
        $service->delete($variantImage);

        return back()->with('success', __('admin.messages.variant_image_deleted'));
    }
}
