<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Catalog\ProductController as CatalogProductController;
use App\Http\Controllers\Catalog\PurchaseController;
use App\Http\Controllers\Checkout\PayPalController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\OrderController as DashboardOrderController;
use App\Http\Controllers\Dashboard\ProductController as DashboardProductController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('home')->middleware('throttle:catalogue');
Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap')->middleware('throttle:catalogue');
Route::get('robots.txt', [SitemapController::class, 'robots'])->name('robots')->middleware('throttle:catalogue');
Route::get('terms', [LegalController::class, 'terms'])->name('terms')->middleware('throttle:catalogue');
Route::get('privacy', [LegalController::class, 'privacy'])->name('privacy')->middleware('throttle:catalogue');
Route::get('pages/{page:slug}', [LegalController::class, 'show'])->name('pages.show')->middleware('throttle:catalogue');

Route::get('apps', [CatalogProductController::class, 'index'])->name('apps.index')->middleware('throttle:catalogue');
Route::get('apps/{product:slug}', [CatalogProductController::class, 'show'])->name('apps.show')->middleware('throttle:catalogue');

Route::middleware(['auth'])->group(function () {
    Route::post('apps/{product:slug}/purchase', [PurchaseController::class, 'store'])->name('apps.purchase')->middleware('throttle:purchase');
    Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index')->middleware('throttle:catalogue');
    Route::get('purchases/{order}', [PurchaseController::class, 'show'])->name('purchases.show')->middleware('throttle:catalogue');
    Route::post('purchases/{order}/pay', [PurchaseController::class, 'pay'])->name('purchases.pay')->middleware('throttle:checkout');
    Route::get('purchases/{order}/download', [PurchaseController::class, 'download'])->name('purchases.download')->middleware('throttle:download');
});

Route::middleware(['auth'])->group(function () {
    Route::get('checkout/paypal/return', [PayPalController::class, 'return'])
        ->name('paypal.return')
        ->middleware('throttle:paypal-return');
    Route::get('checkout/paypal/cancel', [PayPalController::class, 'cancel'])
        ->name('paypal.cancel')
        ->middleware('throttle:paypal-return');
});

Route::post('checkout/paypal/webhook', [PayPalController::class, 'webhook'])
    ->withoutMiddleware(PreventRequestForgery::class)
    ->middleware('throttle:paypal-webhook')
    ->name('paypal.webhook');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('throttle:catalogue');

    Route::resource('dashboard/products', DashboardProductController::class)
        ->parameters(['products' => 'product'])
        ->names('dashboard.products')
        ->middleware('throttle:listing');
    Route::post('dashboard/products/{product}/restore', [DashboardProductController::class, 'restore'])
        ->withTrashed()
        ->name('dashboard.products.restore')
        ->middleware('throttle:listing');
    Route::delete('dashboard/products/{product}/screenshots/{screenshot}', [DashboardProductController::class, 'destroyScreenshot'])
        ->name('dashboard.products.screenshots.destroy')
        ->middleware('throttle:listing');

    Route::get('dashboard/orders', [DashboardOrderController::class, 'index'])->name('dashboard.orders.index')->middleware('throttle:catalogue');
    Route::get('dashboard/orders/{order}', [DashboardOrderController::class, 'show'])->name('dashboard.orders.show')->middleware('throttle:catalogue');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard')->middleware('throttle:catalogue');
    Route::get('users', [UserController::class, 'index'])->name('users.index')->middleware('throttle:catalogue');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show')->middleware('throttle:catalogue');
    Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('throttle:listing');
    Route::post('users/{user}/verify', [UserController::class, 'verify'])->name('users.verify')->middleware('throttle:listing');
    Route::post('users/{user}/unverify', [UserController::class, 'unverify'])->name('users.unverify')->middleware('throttle:listing');
    Route::post('users/{user}/send-reset-link', [UserController::class, 'sendResetLink'])->name('users.send-reset-link')->middleware('throttle:listing');
    Route::post('users/{user}/reset-two-factor', [UserController::class, 'resetTwoFactor'])->name('users.reset-two-factor')->middleware('throttle:listing');
    Route::post('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend')->middleware('throttle:listing');
    Route::post('users/{user}/unsuspend', [UserController::class, 'unsuspend'])->name('users.unsuspend')->middleware('throttle:listing');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('throttle:listing');
    Route::get('products', [ProductController::class, 'index'])->name('products.index')->middleware('throttle:catalogue');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy')->middleware('throttle:listing');
    Route::post('products/{product}/restore', [ProductController::class, 'restore'])->withTrashed()->name('products.restore')->middleware('throttle:listing');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index')->middleware('throttle:catalogue');
    Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index')->middleware('throttle:catalogue');
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit')->middleware('throttle:listing');
    Route::patch('settings', [SettingsController::class, 'update'])->name('settings.update')->middleware('throttle:listing');

    Route::resource('pages', PageController::class)->names('pages')->middleware('throttle:listing');
});

require __DIR__.'/settings.php';
