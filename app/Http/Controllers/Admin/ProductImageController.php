<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductImageRequest;
use App\Http\Requests\Admin\UpdateProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductImageService;
use DomainException;
use Illuminate\Http\RedirectResponse;

class ProductImageController extends Controller
{
    public function store(StoreProductImageRequest $request, Product $product, ProductImageService $productImageService): RedirectResponse
    {
        $productImageService->upload($product, $request->file('images'), $request->validated());

        return back()->with('success', __('admin.messages.product_images_uploaded'));
    }

    public function update(UpdateProductImageRequest $request, ProductImage $productImage, ProductImageService $productImageService): RedirectResponse
    {
        try {
            $productImageService->update($productImage, $request->validated());
        } catch (DomainException $exception) {
            return back()->withErrors(['product_image' => $exception->getMessage()]);
        }

        return back()->with('success', __('admin.messages.product_image_updated'));
    }

    public function setMain(ProductImage $productImage, ProductImageService $productImageService): RedirectResponse
    {
        try {
            $productImageService->setMain($productImage);
        } catch (DomainException $exception) {
            return back()->withErrors(['product_image' => $exception->getMessage()]);
        }

        return back()->with('success', __('admin.messages.product_image_main_updated'));
    }

    public function destroy(ProductImage $productImage, ProductImageService $productImageService): RedirectResponse
    {
        $productImageService->delete($productImage);

        return back()->with('success', __('admin.messages.product_image_deleted'));
    }
}
