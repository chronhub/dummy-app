<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Cart;

use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartOwner;
use Storm\Message\AbstractDomainCommand;

final class CheckoutCart extends AbstractDomainCommand
{
    public static function fromCart(string $cartId, string $cartOwner): self
    {
        return new self([
            'cart_id' => $cartId,
            'cart_owner' => $cartOwner,
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
