<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Exception;

use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartItemId;
use App\Chron\Model\Cart\CartItemSku;

use function sprintf;

class CartNotFound extends CartException
{
    public static function withCartId(CartId $cartId): self
    {
        return new self(sprintf('Cart with id %s not found', $cartId));
    }

    public static function withCartItemId(CartItemId $cartItemId, CartId $cartId): self
    {
        return new self(sprintf('Cart item with id %s not found with cart id %s', $cartItemId, $cartId));
    }

    public static function withCartItemSku(CartItemSku $sku, CartItemId $cartItemId, CartId $cartId): self
    {
        return new self(sprintf('Cart item with sku %s not found in cart %s with id %s', $sku, $cartItemId, $cartId));
    }
}
