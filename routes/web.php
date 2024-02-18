<?php

declare(strict_types=1);

use App\Http\Controllers\Action\ChangeCustomerEmailAction;
use App\Http\Controllers\Action\CustomerCancelOrderAction;
use App\Http\Controllers\Action\MakeOrderAction;
use App\Http\Controllers\Action\RegisterCustomerAction;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\View\Customer\CustomerInfoView;
use App\Http\Controllers\View\Customer\CustomerListView;
use App\Http\Controllers\View\Customer\CustomerOrderView;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/customer', CustomerListView::class)->name('customer.list');
Route::get('/customer/{customer_id}', CustomerInfoView::class)->name('customer.info.show');
Route::get('/customer/{customer_id}/order/{order_id}', CustomerOrderView::class)->name('customer.order.show');

// seed
Route::group(['prefix' => 'seed'], function () {
    Route::get('/customer', RegisterCustomerAction::class)->name('seed.customer.register');
    Route::get('/customer/email/change', ChangeCustomerEmailAction::class)->name('seed.customer.email.change');
    Route::get('/order/random', MakeOrderAction::class)->name('seed.order.random');
    // todo seed order for customer and order
    Route::get('/customer/{customer_id}/order/{order_id}/cancel', CustomerCancelOrderAction::class)->name('seed.order.cancel');
});
