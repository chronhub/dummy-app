<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Exception;

use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartItemId;
use App\Chron\Model\Cart\CartItemSku;

use function sprintf;

class CartItemNotFound extends CartNotFound
{
    public static function withCartItem(CartItemSku $sku, CartItemId $cartItemId, CartId $cartId): self
    {
        return new self(sprintf('Cart item with sku "%s" not found with item id "%s" in cart id "%s"', $sku, $cartItemId, $cartId));
    }
}
