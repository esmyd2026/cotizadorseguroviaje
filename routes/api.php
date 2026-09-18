<?php

use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\PhoneValidationController;
use App\Http\Controllers\Api\QuoteController;
use Illuminate\Support\Facades\Route;

Route::get('/countries', [CountryController::class, 'index'])->name('api.countries.index');
Route::post('/phone/validate', PhoneValidationController::class)->name('api.phone.validate');

Route::post('/quotes', [QuoteController::class, 'store'])->name('api.quotes.store');
Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('api.quotes.show');
Route::post('/quotes/{quote}/payment', [QuoteController::class, 'payment'])->name('api.quotes.payment');
Route::get('/quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('api.quotes.pdf');
