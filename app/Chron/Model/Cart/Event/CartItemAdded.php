<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Event;

use App\Chron\Model\Cart\CartBalance;
use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartItem;
use App\Chron\Model\Cart\CartOwner;
use App\Chron\Model\Cart\CartQuantity;
use Storm\Message\AbstractDomainEvent;

final class CartItemAdded extends AbstractDomainEvent
{
    public static function forCart(
        CartId $cartId,
        CartOwner $cartOwner,
        CartItem $cartItem,
        CartBalance $cartBalance,
        CartQuantity $cartQuantity
    ): self {
        return new self([
            'cart_id' => $cartId->toString(),
            'cart_owner' => $cartOwner->toString(),
            'cart_balance' => $cartBalance->value,
            'cart_quantity' => $cartQuantity->value,
            'cart_item_id' => $cartItem->id->toString(),
            'cart_item_sku' => $cartItem->sku->toString(),
            'cart_item_price' => $cartItem->price->value,
            'cart_item_quantity' => $cartItem->quantity->value,
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

    public function cartBalance(): CartBalance
    {
        return CartBalance::fromString($this->content['cart_balance']);
    }

    public function cartQuantity(): CartQuantity
    {
        return CartQuantity::fromInteger($this->content['cart_quantity']);
    }

    public function cartItem(): CartItem
    {
        return CartItem::fromArray($this->content);
    }
}
