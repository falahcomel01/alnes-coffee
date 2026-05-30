<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\KitchenController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PromoController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\TableController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me',     [AuthController::class, 'me']);
        });
    });

    // Settings (public)
    Route::get('/settings', [SettingController::class, 'index']);

    // Banners (public)
    Route::get('/banners', [BannerController::class, 'index']);

    // Categories (public)
    Route::prefix('categories')->group(function () {
        Route::get('/',       [CategoryController::class, 'index']);
        Route::get('/{slug}', [CategoryController::class, 'show']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/',       [CategoryController::class, 'store']);
            Route::put('/{id}',    [CategoryController::class, 'update']);
            Route::delete('/{id}', [CategoryController::class, 'destroy']);
        });
    });

    // Products (public)
    Route::prefix('products')->group(function () {
        Route::get('/featured', [ProductController::class, 'featured']);
        Route::get('/search',   [ProductController::class, 'search']);
        Route::get('/',         [ProductController::class, 'index']);
        Route::get('/{slug}',   [ProductController::class, 'show']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/',       [ProductController::class, 'store']);
            Route::put('/{id}',    [ProductController::class, 'update']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
        });
    });

    // Tables (public)
    Route::get('/tables/{slug}', [TableController::class, 'show']);

    // Promos (public)
    Route::post('/promos/check', [PromoController::class, 'check']);

    // Orders (public)
    Route::prefix('orders')->group(function () {
        Route::post('/',         [OrderController::class, 'store']);
        Route::get('/{invoice}', [OrderController::class, 'show']);
    });

    // Payment Midtrans
    Route::post('/payment/token',   [PaymentController::class, 'createToken']);
    Route::post('/payment/webhook', [PaymentController::class, 'webhook']);

    // Kitchen Display
    Route::get('/kitchen', [KitchenController::class, 'index']);
    Route::patch('/kitchen/{id}/status', [KitchenController::class, 'updateStatus']);

});