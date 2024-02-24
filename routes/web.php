<?php

declare(strict_types=1);

use App\Http\Controllers\Action\AddOrderItemAction;
use App\Http\Controllers\Action\ChangeCustomerEmailAction;
use App\Http\Controllers\Action\CustomerCancelOrderAction;
use App\Http\Controllers\Action\MakeOrderAction;
use App\Http\Controllers\Action\RegisterCustomerAction;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\View\Catalog\CatalogView;
use App\Http\Controllers\View\Customer\CustomerInfoView;
use App\Http\Controllers\View\Customer\CustomerListView;
use App\Http\Controllers\View\Customer\CustomerOrderHistoryView;
use App\Http\Controllers\View\Customer\CustomerOrderView;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('dashboard');

Route::get('/customer', CustomerListView::class)->name('customer.list');

Route::get('/customer/{customer_id}', CustomerInfoView::class)->name('customer.info.show');

Route::get('/customer/{customer_id}/order/{order_id}/history', CustomerOrderHistoryView::class)->name('customer.order.history.show');

Route::get('/customer/{customer_id}/order/{order_id}', CustomerOrderView::class)->name('customer.order.show');

Route::get('/catalog', CatalogView::class)->name('catalog');

// seed
Route::group(['prefix' => 'seed'], function () {
    Route::get('/customer', RegisterCustomerAction::class)->name('seed.customer.register');

    Route::get('/customer/email/change', ChangeCustomerEmailAction::class)->name('seed.customer.email.change');

    Route::get('/order/random', MakeOrderAction::class)->name('seed.order.random');

    Route::get('/customer/{customer_id}/order/{order_id}/cancel', CustomerCancelOrderAction::class)->name('seed.order.cancel');

    Route::get('/customer/{customer_id}/order/{order_id}/add', AddOrderItemAction::class)->name('seed.order.add');
});
