<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Exception;

use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartItemId;

use function sprintf;

class CartItemAlreadyExists extends CartAlreadyExists
{
    public static function withCartItemId(CartItemId $cartItemId, CartId $cartId): self
    {
        return new self(sprintf('Cart item with id %s already exists in cart id %s', $cartItemId, $cartId));
    }
}
