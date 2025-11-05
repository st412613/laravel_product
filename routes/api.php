<?php

use Illuminate\Support\Facades\Route;




Route::apiResource('users', App\Http\Controllers\UserController::class);

Route::apiResource('products', App\Http\Controllers\ProductController::class);

Route::apiResource('currencies', App\Http\Controllers\CurrencyController::class);

Route::apiResource('prices', App\Http\Controllers\PriceController::class);
