<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Exception;

use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartItemSku;

use function sprintf;

class InsufficientStockForCartItem extends CartException
{
    public static function withId(CartId $cartId, CartItemSku $skuId): self
    {
        return new self(sprintf(
            'Insufficient stock for sku item "%s" in cart "%s"',
            $skuId,
            $cartId
        ));
    }
}
