<?php

use App\Http\Controllers\Admin\QuoteController as AdminQuoteController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Customer\QuoteController as CustomerQuoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('wizard');
})->name('wizard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('/password/reset/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/password/reset', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/admin/quotes', [AdminQuoteController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('admin.quotes.index');

Route::get('/mis-cotizaciones', [CustomerQuoteController::class, 'index'])
    ->middleware('auth')
    ->name('customer.quotes.index');
