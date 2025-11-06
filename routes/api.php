<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('users', UserController::class)->except(['index']);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('currencies', CurrencyController::class);
    Route::apiResource('prices', PriceController::class);
});

