<?php

use App\Http\Controllers\Frontend\PageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/payment-requests');

Route::get('/login', [PageController::class, 'login'])->name('login');
Route::get('/register', [PageController::class, 'register'])->name('register');
Route::get('/payment-requests', [PageController::class, 'paymentRequestsIndex'])->name('payment-requests.index');
Route::get('/payment-requests/create', [PageController::class, 'paymentRequestsCreate'])->name('payment-requests.create');
Route::get('/payment-requests/{paymentRequest}', [PageController::class, 'paymentRequestsShow'])->name('payment-requests.show');
