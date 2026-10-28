<?php

use App\Http\Controllers\OrderPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [OrderPageController::class, 'index'])
    ->name('orders.index');

Route::post('/orders', [OrderPageController::class, 'store'])
    ->name('orders.store');

Route::post('/orders/rows', [OrderPageController::class, 'updateRows'])
    ->name('orders.rows');
