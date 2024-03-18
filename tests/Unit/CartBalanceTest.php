<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Chron\Model\Cart\CartBalance;
use App\Chron\Model\Cart\CartItemPrice;
use App\Chron\Model\Cart\CartItemQuantity;

it('can create a cart balance from zero', function () {
    $cartBalance = CartBalance::fromDefault();

    expect($cartBalance->value)->toBe('0.00');
});

it('can add a cart item price and quantity to the cart balance', function (string $price, int $quantity, string $expected) {
    $cartBalance = CartBalance::fromDefault();
    $cartItemPrice = CartItemPrice::fromString($price);
    $cartItemQuantity = CartItemQuantity::fromInteger($quantity);

    $cartBalance = $cartBalance->add($cartItemPrice, $cartItemQuantity);

    expect($cartBalance->value)->toBe($expected);
})->with([
    ['0.99', 3, '2.97'],
    ['1.00', 1, '1.00'],
    ['10.50', 2, '21.00'],
]);
