<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\TaxClassController;
use App\Http\Controllers\Admin\TaxRateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Storefront\ProductCatalogController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/products')->name('home');
Route::get('/products', ProductCatalogController::class)->name('products.index');

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
    Route::post('/products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
    Route::put('/product-images/{productImage}', [ProductImageController::class, 'update'])->name('product-images.update');
    Route::put('/product-images/{productImage}/set-main', [ProductImageController::class, 'setMain'])->name('product-images.set-main');
    Route::delete('/product-images/{productImage}', [ProductImageController::class, 'destroy'])->name('product-images.destroy');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{inventoryStock}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{inventoryStock}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::post('/inventory/{inventoryStock}/adjust', [InventoryController::class, 'update'])->name('inventory.update');
    Route::get('/inventory/{inventoryStock}/logs', [InventoryController::class, 'logs'])->name('inventory.logs');
});

require __DIR__.'/auth.php';
