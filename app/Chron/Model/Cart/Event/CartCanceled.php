<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart\Event;

use App\Chron\Model\Cart\CartBalance;
use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartOwner;
use App\Chron\Model\Cart\CartQuantity;
use App\Chron\Model\Cart\CartStatus;
use Storm\Message\AbstractDomainEvent;

final class CartCanceled extends AbstractDomainEvent
{
    public static function forCart(
        CartId $cartId,
        CartOwner $cartOwner,
        CartBalance $newCartBalance,
        CartQuantity $newCartQuantity,
        CartBalance $oldCartBalance,
        CartQuantity $oldCartQuantity,
        CartStatus $newCartStatus,
        CartStatus $oldCartStatus
    ): self {
        return new self([
            'cart_id' => $cartId->toString(),
            'cart_owner' => $cartOwner->toString(),
            'new_cart_balance' => $newCartBalance->value,
            'new_cart_quantity' => $newCartQuantity->value,
            'old_cart_balance' => $oldCartBalance->value,
            'old_cart_quantity' => $oldCartQuantity->value,
            'new_cart_status' => $newCartStatus->value,
            'old_cart_status' => $oldCartStatus->value,
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

    public function newCartStatus(): CartStatus
    {
        return CartStatus::from($this->content['new_cart_status']);
    }

    public function oldCartStatus(): CartStatus
    {
        return CartStatus::from($this->content['old_cart_status']);
    }

    public function newCartBalance(): CartBalance
    {
        return CartBalance::fromString($this->content['new_cart_balance']);
    }

    public function oldCartBalance(): CartBalance
    {
        return CartBalance::fromString($this->content['old_cart_balance']);
    }

    public function newCartQuantity(): CartQuantity
    {
        return CartQuantity::fromInteger($this->content['new_cart_quantity']);
    }

    public function oldCartQuantity(): CartQuantity
    {
        return CartQuantity::fromInteger($this->content['old_cart_quantity']);
    }
}
