<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Publicly accessible product listing route
Route::get('/products', [ProductController::class, 'index']);

//Cart Route
Route::post('/cart', [CartController::class, 'store']);

// In routes/api.php
Route::get('/cart', [CartController::class, 'index']);

// In routes/api.php
Route::put('/cart/{cartItem}', [CartController::class, 'update']);

// In routes/api.php
Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);

// In routes/api.php
Route::post('/checkout', [CheckoutController::class, 'process']);

// Add this new route for processing the order
Route::post('/checkout', [CheckoutController::class, 'placeOrder'])->name('checkout.place.order');
Route::post('/checkout/order', [CheckoutController::class, 'createRazorpayOrder'])->name('api.checkout.order');
Route::post('/checkout/verify', [CheckoutController::class, 'verifyPayment'])->name('api.checkout.verify');