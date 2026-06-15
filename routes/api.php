<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\PaymentRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('/finance-only', function () {
        return response()->json(['message' => 'ok']);
    })->middleware(['auth:api', 'role:finance']);
});

Route::get('/currencies', [CurrencyController::class, 'index'])
    ->middleware('auth:api');

Route::get('/payment-requests', [PaymentRequestController::class, 'index'])
    ->middleware('auth:api');
Route::post('/payment-requests', [PaymentRequestController::class, 'store'])
    ->middleware(['auth:api', 'role:employee']);
Route::post('/payment-requests/{paymentRequest}/approve', [PaymentRequestController::class, 'approve'])
    ->middleware('auth:api');
Route::post('/payment-requests/{paymentRequest}/reject', [PaymentRequestController::class, 'reject'])
    ->middleware('auth:api');
Route::get('/payment-requests/{paymentRequest}', [PaymentRequestController::class, 'show'])
    ->middleware('auth:api');
