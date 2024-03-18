<?php

declare(strict_types=1);

use App\Http\Controllers\Action\Cart\AddCartItemAction;
use App\Http\Controllers\Action\Cart\CancelCartItemAction;
use App\Http\Controllers\Action\Cart\CheckoutCartAction;
use App\Http\Controllers\Action\Cart\PayCartAction;
use App\Http\Controllers\Action\Cart\RemoveCartItemAction;
use App\Http\Controllers\Action\Cart\UpdateCartItemQuantityAction;
use App\Http\Controllers\Action\Complete\CheckoutAllCartAction;
use App\Http\Controllers\Action\Complete\PayAllSubmittedCartAction;
use App\Http\Controllers\Action\Random\Cart\AddRandomCartItemAction;
use App\Http\Controllers\Action\Random\Cart\CheckoutCartRandomAction;
use App\Http\Controllers\Action\Random\Cart\PayOrderRandomAction;
use App\Http\Controllers\Action\Random\Cart\RemoveRandomCartItemAction;
use App\Http\Controllers\Action\Random\Cart\UpdateRandomCartItemQuantityAction;
use App\Http\Controllers\Action\Random\Customer\ChangeRandomCustomerEmailAction;
use App\Http\Controllers\Action\Random\Customer\RegisterRandomCustomerAction;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\View\Catalog\CatalogView;
use App\Http\Controllers\View\Customer\CustomerCartHistory;
use App\Http\Controllers\View\Customer\CustomerCartView;
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

Route::get('/customer/{customer_id}/cart/{cart_id}/', CustomerCartView::class)->name('customer.cart.view');
Route::get('/customer/{customer_id}/cart/{cart_id}/history', CustomerCartHistory::class)->name('customer.cart.history');
Route::get('/customer/{customer_id}/cart/{cart_id}/cancel', CancelCartItemAction::class)->name('customer.cart.cancel');
Route::get('/customer/{customer_id}/cart/{cart_id}/checkout', CheckoutCartAction::class)->name('customer.cart.checkout');
Route::get('/customer/{customer_id}/cart/{cart_id}/pay', PayCartAction::class)->name('customer.cart.pay');
Route::get('/customer/{customer_id}/cart/{cart_id}/add/{sku}/{quantity}', AddCartItemAction::class)->name('customer.cart.add');
Route::get('/customer/{customer_id}/cart/{cart_id}/{cart_item_id}/remove/{sku}', RemoveCartItemAction::class)->name('customer.cart.remove');
Route::get('/customer/{customer_id}/cart/{cart_id}/{cart_item_id}/update/{sku}/{quantity}', UpdateCartItemQuantityAction::class)->name('customer.cart.update');

Route::get('/catalog', CatalogView::class)->name('catalog');

// seed
Route::group(['prefix' => 'seed'], function () {
    Route::get('/customer', RegisterRandomCustomerAction::class);
    Route::get('/customer/email/change', ChangeRandomCustomerEmailAction::class);

    Route::get('/cart/all/checkout', CheckoutAllCartAction::class);
    Route::get('/cart/all/pay', PayAllSubmittedCartAction::class);

    Route::get('/cart/add', AddRandomCartItemAction::class);
    Route::get('/cart/remove', RemoveRandomCartItemAction::class);
    Route::get('/cart/update', UpdateRandomCartItemQuantityAction::class);
    Route::get('/cart/checkout', CheckoutCartRandomAction::class);
    Route::get('/cart/pay', PayOrderRandomAction::class);
});
