<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Command\Cart;

use App\Chron\Model\Cart\CartId;
use App\Chron\Model\Cart\CartOwner;
use Storm\Message\AbstractDomainCommand;

final class OpenCart extends AbstractDomainCommand
{
    public static function forCustomer(string $cartOwner, string $cartId): self
    {
        return new self([
            'cart_owner' => $cartOwner,
            'cart_id' => $cartId,
        ]);
    }

    public function customerId(): CartOwner
    {
        return CartOwner::fromString($this->content['cart_owner']);
    }

    public function cartId(): CartId
    {
        return CartId::fromString($this->content['cart_id']);
    }
}
