<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Event;

use App\Chron\Model\Cart\CartBalance;
use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartOwner;
use App\Chron\Model\Cart\CartQuantity;
use App\Chron\Model\Cart\CartStatus;
use Storm\Message\AbstractDomainEvent;

final class CartOpened extends AbstractDomainEvent
{
    public static function forOwner(
        CartId $cartId,
        CartOwner $cartOwner,
        CartStatus $cartStatus,
        CartBalance $cartBalance,
        CartQuantity $cartQuantity
    ): self {
        return new self(
            [
                'cart_id' => $cartId->toString(),
                'cart_owner' => $cartOwner->toString(),
                'cart_status' => $cartStatus->value,
                'cart_balance' => $cartBalance->value,
                'cart_quantity' => $cartQuantity->value,
            ]
        );
    }

    public function aggregateId(): CartId
    {
        return CartId::fromString($this->content['cart_id']);
    }

    public function cartOwner(): CartOwner
    {
        return CartOwner::fromString($this->content['cart_owner']);
    }

    public function cartStatus(): CartStatus
    {
        return CartStatus::from($this->content['cart_status']);
    }

    public function cartBalance(): CartBalance
    {
        return CartBalance::fromString($this->content['cart_balance']);
    }

    public function cartQuantity(): CartQuantity
    {
        return CartQuantity::fromInteger($this->content['cart_quantity']);
    }
}
