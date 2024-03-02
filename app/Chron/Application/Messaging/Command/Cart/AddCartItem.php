<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Cart;

use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartOwner;
use Storm\Message\AbstractDomainCommand;

final class AddCartItem extends AbstractDomainCommand
{
    public static function toCart(
        string $cartId,
        string $cartOwner,
        string $sku,
        string $price,
        int $quantity
    ): self {
        return new self([
            'cart_owner' => $cartOwner,
            'cart_id' => $cartId,
            'cart_item_sku' => $sku,
            'cart_item_price' => $price,
            'cart_item_quantity' => $quantity,
        ]);
    }

    public function cartId(): CartId
    {
        return CartId::fromString($this->content['cart_id']);
    }

    public function cartOwner(): CartOwner
    {
        return CartOwner::fromString($this->content['cart_owner']);
    }
}
