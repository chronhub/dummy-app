<?php

declare(strict_types=1);

use App\Http\Controllers\Customer\CustomerInfoAction;
use App\Http\Controllers\Customer\CustomerListAction;
use App\Http\Controllers\Customer\CustomerOrderAction;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Seed\RegisterCustomerAction;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/customer', CustomerListAction::class)->name('customer.list');
Route::get('/customer/{customer_id}', CustomerInfoAction::class)->name('customer.info.show');
Route::get('/customer/{customer_id}/order/{order_id}', CustomerOrderAction::class)->name('customer.order.show');

// seed
Route::group(['prefix' => 'seed'], function () {
    Route::get('/customer', RegisterCustomerAction::class)->name('seed.customer.register');
    Route::get('/customer/email/change', RegisterCustomerAction::class)->name('seed.customer.email.change');
    Route::get('/order/random', RegisterCustomerAction::class)->name('seed.order.random');
});
