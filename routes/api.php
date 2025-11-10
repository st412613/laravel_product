<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\PriceController;

Route::post('/register', [AuthController::class, 'register'])
    ->withoutMiddleware(['auth:sanctum', \App\Http\Middleware\CheckTokenExpiry::class]);

Route::post('/login', [AuthController::class, 'login'])
    ->withoutMiddleware(['auth:sanctum', \App\Http\Middleware\CheckTokenExpiry::class]);


Route::post('/logout', [AuthController::class, 'logout']);
Route::apiResource('users', UserController::class)->except(['index']);
Route::apiResource('products', ProductController::class);
Route::apiResource('currencies', CurrencyController::class);
Route::apiResource('prices', PriceController::class);
