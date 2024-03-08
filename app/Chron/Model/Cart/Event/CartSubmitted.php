<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Event;

use App\Chron\Model\Cart\CartBalance;
use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartOwner;
use App\Chron\Model\Cart\CartQuantity;
use App\Chron\Model\Cart\CartStatus;
use Storm\Message\AbstractDomainEvent;

final class CartSubmitted extends AbstractDomainEvent
{
    public static function forCart(
        CartId $cartId,
        CartOwner $cartOwner,
        CartBalance $cartBalance,
        CartQuantity $cartQuantity,
        CartStatus $cartStatus
    ): self {
        return new self([
            'cart_id' => $cartId->toString(),
            'cart_owner' => $cartOwner->toString(),
            'cart_balance' => $cartBalance->value,
            'cart_quantity' => $cartQuantity->value,
            'cart_status' => $cartStatus->value,
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

    public function cartStatus(): CartStatus
    {
        return CartStatus::from($this->content['cart_status']);
    }
}
