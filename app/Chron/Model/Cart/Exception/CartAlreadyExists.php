<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Exception;

use App\Chron\Model\Cart\CartId;

use function sprintf;

class CartAlreadyExists extends CartException
{
    public static function withCartId(CartId $cartId): self
    {
        return new self(sprintf('Cart with id %s already exists', $cartId));
    }
}
