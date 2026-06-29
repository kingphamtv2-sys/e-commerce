<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\EmailNotificationSettingsController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\OnlinePaymentSettingsController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProductOptionController;
use App\Http\Controllers\Admin\ProductOptionValueController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ShippingMethodController;
use App\Http\Controllers\Admin\ShippingZoneController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\TaxClassController;
use App\Http\Controllers\Admin\TaxRateController;
use App\Http\Controllers\Admin\ThemeSettingsController;
use App\Http\Controllers\Admin\VariantImageController;
use App\Http\Controllers\CustomerAddressController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\CustomerPasswordController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\GuestOrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CartCouponController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\CheckoutShippingController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\MockPaymentGatewayController;
use App\Http\Controllers\Storefront\OnlinePaymentController;
use App\Http\Controllers\Storefront\OrderCreationController;
use App\Http\Controllers\Storefront\PaymentCodController;
use App\Http\Controllers\Storefront\PaymentResultController;
use App\Http\Controllers\Storefront\PaymentReturnController;
use App\Http\Controllers\Storefront\PaymentWebhookController;
use App\Http\Controllers\Storefront\ProductCatalogController;
use App\Http\Controllers\Storefront\ProductDetailController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/products', ProductCatalogController::class)->name('products.index');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/summary', [CartController::class, 'summary'])->name('cart.summary');
Route::post('/cart/coupon', [CartCouponController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('cart.coupon.apply');
Route::delete('/cart/coupon', [CartCouponController::class, 'destroy'])->name('cart.coupon.remove');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/checkout/summary', [CheckoutController::class, 'summary'])->name('checkout.summary');
Route::get('/checkout/shipping-methods', [CheckoutShippingController::class, 'methods'])->name('checkout.shipping.methods');
Route::post('/checkout/shipping-method', [CheckoutShippingController::class, 'select'])->name('checkout.shipping.select');
Route::post('/checkout/shipping/recalculate', [CheckoutShippingController::class, 'recalculate'])->name('checkout.shipping.recalculate');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/payment/{token}', [PaymentCodController::class, 'show'])->name('checkout.payment.show');
Route::post('/checkout/payment/{token}/cod', [PaymentCodController::class, 'store'])->name('checkout.payment.cod');
Route::post('/checkout/payment/{token}/online', [OnlinePaymentController::class, 'select'])->name('checkout.payment.online');
Route::post('/checkout/order/{token}/pay', [OnlinePaymentController::class, 'placeAndPay'])
    ->middleware('throttle:5,1')
    ->name('checkout.order.pay');
Route::post('/checkout/order/{token}', [OrderCreationController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('checkout.order.store');
Route::get('/orders/success/{token}', [OrderCreationController::class, 'success'])->name('orders.success');
Route::post('/orders/{order}/payment/retry', [OnlinePaymentController::class, 'retry'])
    ->middleware('throttle:5,1')
    ->name('orders.payment.retry');
Route::get('/payment/mock', [MockPaymentGatewayController::class, 'show'])->name('payment.mock.show');
Route::get('/payment/mock/{transaction}/{status}', [MockPaymentGatewayController::class, 'complete'])->name('payment.mock.complete');
Route::match(['get', 'post'], '/payment/return/{gateway}', PaymentReturnController::class)->name('payment.return');
Route::post('/payment/webhook/{gateway}', PaymentWebhookController::class)->name('payment.webhook');
Route::get('/payment/result/{order}', [PaymentResultController::class, 'show'])->name('payment.result');
Route::get('/payment/error', [PaymentResultController::class, 'error'])->name('payment.error');
Route::get('/products/{slug}', ProductDetailController::class)->name('products.show');
Route::get('/guest-orders/{token}', [GuestOrderController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('guest.orders.show');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route(
        request()->user()->role === 'customer' ? 'account.index' : 'admin.dashboard',
    ))->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('account')->name('account.')->middleware(['auth', 'customer'])->group(function () {
    Route::get('/', AccountController::class)->name('index');
    Route::get('/profile', [CustomerProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [CustomerProfileController::class, 'update'])->name('profile.update');
    Route::get('/password', [CustomerPasswordController::class, 'edit'])->name('password.edit');
    Route::patch('/password', [CustomerPasswordController::class, 'update'])->name('password.update');
    Route::get('/addresses', [CustomerAddressController::class, 'index'])->name('addresses.index');
    Route::get('/addresses/create', [CustomerAddressController::class, 'create'])->name('addresses.create');
    Route::post('/addresses', [CustomerAddressController::class, 'store'])->name('addresses.store');
    Route::get('/addresses/{address}/edit', [CustomerAddressController::class, 'edit'])->name('addresses.edit');
    Route::patch('/addresses/{address}', [CustomerAddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [CustomerAddressController::class, 'destroy'])->name('addresses.destroy');
    Route::patch('/addresses/{address}/default', [CustomerAddressController::class, 'setDefault'])->name('addresses.default');
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'admin.locale'])->group(function () {
    Route::redirect('/', '/admin/dashboard')->name('index');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SystemSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SystemSettingController::class, 'update'])->name('settings.update');
    Route::get('/settings/email', [EmailNotificationSettingsController::class, 'edit'])->name('settings.email.edit');
    Route::patch('/settings/email', [EmailNotificationSettingsController::class, 'update'])->name('settings.email.update');
    Route::post('/settings/email/test', [EmailNotificationSettingsController::class, 'test'])
        ->middleware('throttle:3,1')
        ->name('settings.email.test');
    Route::get('/settings/payment/online', [OnlinePaymentSettingsController::class, 'edit'])->name('settings.payment.online.edit');
    Route::patch('/settings/payment/online', [OnlinePaymentSettingsController::class, 'update'])->name('settings.payment.online.update');
    Route::get('/theme', [ThemeSettingsController::class, 'edit'])->name('theme.edit');
    Route::patch('/theme', [ThemeSettingsController::class, 'update'])->name('theme.update');
    Route::delete('/theme/reset', [ThemeSettingsController::class, 'reset'])->name('theme.reset');
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
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status.update');
    Route::patch('/orders/{order}/payment', [OrderController::class, 'updatePayment'])->name('orders.payment.update');
    Route::patch('/orders/{order}/fulfillment', [OrderController::class, 'updateFulfillment'])->name('orders.fulfillment.update');
    Route::post('/orders/{order}/mark-paid', [OrderController::class, 'markPaid'])->name('orders.mark-paid');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/notes', [OrderController::class, 'storeNote'])->name('orders.notes.store');
    Route::resource('shipping/zones', ShippingZoneController::class)->names('shipping.zones')->except('show');
    Route::resource('shipping/methods', ShippingMethodController::class)->names('shipping.methods')->except('show');
    Route::resource('coupons', CouponController::class)->except('show');
    Route::resource('banners', BannerController::class)->except('show');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/orders', [ReportController::class, 'orders'])->name('reports.orders');
    Route::get('/reports/product-sales', [ReportController::class, 'productSales'])->name('reports.product-sales');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/coupons', [ReportController::class, 'coupons'])->name('reports.coupons');
    Route::get('/reports/taxes', [ReportController::class, 'taxes'])->name('reports.taxes');
    Route::get('/reports/payments', [ReportController::class, 'payments'])->name('reports.payments');
    Route::get('/reports/{report}/export', [ReportController::class, 'export'])->name('reports.export');
});

require __DIR__.'/auth.php';
