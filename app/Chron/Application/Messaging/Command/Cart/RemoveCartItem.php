<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Cart;

use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartItemId;
use App\Chron\Model\Cart\CartItemSku;
use App\Chron\Model\Cart\CartOwner;
use Storm\Message\AbstractDomainCommand;

final class RemoveCartItem extends AbstractDomainCommand
{
    public static function forCart(
        string $cartItemId,
        string $cartId,
        string $cartOwner,
        string $sku,
    ): self {
        return new self([
            'cart_owner' => $cartOwner,
            'cart_id' => $cartId,
            'cart_item_id' => $cartItemId,
            'cart_item_sku' => $sku,
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

    public function cartItemSku(): CartItemSku
    {
        return CartItemSku::fromString($this->content['cart_item_sku']);
    }

    public function cartItemId(): CartItemId
    {
        return CartItemId::fromString($this->content['cart_item_id']);
    }
}
