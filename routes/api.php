<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RfidController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API for Cashier System
Route::get('/products', [ProductController::class, 'index']);
Route::post('/check-card', [RfidController::class, 'check']);
Route::post('/pay', [TransactionController::class, 'pay']);
