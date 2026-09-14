<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BrandStatusController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CategoryStatusController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\OfferStatusController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductGroupController;
use App\Http\Controllers\Admin\ProductGroupStatusController;
use App\Http\Controllers\Admin\ProductStatusController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\StoreSourceController;
use App\Http\Controllers\Admin\StoreSourceStatusController;
use App\Http\Controllers\Admin\StoreStatusController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::middleware('can:manage categories')->group(function (): void {
    Route::resource('categories', CategoryController::class)->except('destroy');
    Route::patch('categories/{category}/status', CategoryStatusController::class)->name('categories.status');
});

Route::middleware('can:manage brands')->group(function (): void {
    Route::resource('brands', BrandController::class)->except('destroy');
    Route::patch('brands/{brand}/status', BrandStatusController::class)->name('brands.status');
});

Route::middleware('can:manage products')->group(function (): void {
    Route::resource('product-groups', ProductGroupController::class)->except('destroy');
    Route::patch('product-groups/{product_group}/status', ProductGroupStatusController::class)
        ->name('product-groups.status');

    Route::resource('products', ProductController::class)->except('destroy');
    Route::patch('products/{product}/status', ProductStatusController::class)->name('products.status');
});

Route::middleware('can:manage offers')->group(function (): void {
    Route::resource('offers', OfferController::class)->except('destroy');
    Route::patch('offers/{offer}/status', OfferStatusController::class)->name('offers.status');
});

Route::middleware('can:manage stores')->scopeBindings()->group(function (): void {
    Route::resource('stores', StoreController::class)->except('destroy');
    Route::patch('stores/{store}/status', StoreStatusController::class)->name('stores.status');

    Route::resource('stores.sources', StoreSourceController::class)
        ->parameters(['sources' => 'source'])
        ->except('destroy');
    Route::patch('stores/{store}/sources/{source}/status', StoreSourceStatusController::class)
        ->name('stores.sources.status');
});
