<?php

declare(strict_types=1);

use App\Chron\Application\Api\AddCartItemApi;
use App\Chron\Application\Api\RegisterCustomerApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/customer/register', RegisterCustomerApi::class);
Route::post('/cart/add', AddCartItemApi::class);
Route::post('/cart/update', \App\Chron\Application\Api\UpdateCartItemApi::class);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
