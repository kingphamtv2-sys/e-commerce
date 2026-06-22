<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProductOptionController;
use App\Http\Controllers\Admin\ProductOptionValueController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\TaxClassController;
use App\Http\Controllers\Admin\TaxRateController;
use App\Http\Controllers\Admin\VariantImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CartCouponController;
use App\Http\Controllers\Storefront\ProductCatalogController;
use App\Http\Controllers\Storefront\ProductDetailController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/products')->name('home');
Route::get('/products', ProductCatalogController::class)->name('products.index');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/summary', [CartController::class, 'summary'])->name('cart.summary');
Route::post('/cart/coupon', [CartCouponController::class, 'store'])->name('cart.coupon.apply');
Route::delete('/cart/coupon', [CartCouponController::class, 'destroy'])->name('cart.coupon.remove');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
Route::get('/products/{slug}', ProductDetailController::class)->name('products.show');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('account.index'))->name('dashboard');
    Route::get('/account', AccountController::class)->name('account.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'admin.locale'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SystemSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SystemSettingController::class, 'update'])->name('settings.update');
    Route::put('/languages/{language}/set-default', [LanguageController::class, 'setDefault'])->name('languages.set-default');
    Route::resource('languages', LanguageController::class)->except('show');
    Route::put('/currencies/{currency}/set-default', [CurrencyController::class, 'setDefault'])->name('currencies.set-default');
    Route::resource('currencies', CurrencyController::class)->except('show');
    Route::resource('tax-classes', TaxClassController::class)->except('show');
    Route::resource('tax-rates', TaxRateController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('products', ProductController::class)->except('show');
    Route::post('/products/{product}/options', [ProductOptionController::class, 'store'])->name('products.options.store');
    Route::put('/product-options/{productOption}', [ProductOptionController::class, 'update'])->name('product-options.update');
    Route::delete('/product-options/{productOption}', [ProductOptionController::class, 'destroy'])->name('product-options.destroy');
    Route::post('/product-options/{productOption}/values', [ProductOptionValueController::class, 'store'])->name('product-options.values.store');
    Route::put('/product-option-values/{productOptionValue}', [ProductOptionValueController::class, 'update'])->name('product-option-values.update');
    Route::delete('/product-option-values/{productOptionValue}', [ProductOptionValueController::class, 'destroy'])->name('product-option-values.destroy');
    Route::post('/products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
    Route::put('/product-variants/{productVariant}', [ProductVariantController::class, 'update'])->name('product-variants.update');
    Route::delete('/product-variants/{productVariant}', [ProductVariantController::class, 'destroy'])->name('product-variants.destroy');
    Route::get('/product-variants/{productVariant}/images', [VariantImageController::class, 'index'])->name('product-variants.images.index');
    Route::post('/product-variants/{productVariant}/images', [VariantImageController::class, 'store'])->name('product-variants.images.store');
    Route::put('/variant-images/{variantImage}', [VariantImageController::class, 'update'])->name('variant-images.update');
    Route::post('/variant-images/{variantImage}/set-main', [VariantImageController::class, 'setMain'])->name('variant-images.set-main');
    Route::delete('/variant-images/{variantImage}', [VariantImageController::class, 'destroy'])->name('variant-images.destroy');
    Route::post('/products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
    Route::put('/product-images/{productImage}', [ProductImageController::class, 'update'])->name('product-images.update');
    Route::put('/product-images/{productImage}/set-main', [ProductImageController::class, 'setMain'])->name('product-images.set-main');
    Route::delete('/product-images/{productImage}', [ProductImageController::class, 'destroy'])->name('product-images.destroy');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{inventoryStock}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{inventoryStock}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::post('/inventory/{inventoryStock}/adjust', [InventoryController::class, 'update'])->name('inventory.update');
    Route::get('/inventory/{inventoryStock}/logs', [InventoryController::class, 'logs'])->name('inventory.logs');
    Route::resource('coupons', CouponController::class)->except('show');
});

require __DIR__.'/auth.php';
