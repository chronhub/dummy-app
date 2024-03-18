<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart;

use App\Chron\Model\Cart\Exception\InvalidCartValue;

final class CartItemQuantity
{
    private function __construct(public int $value)
    {
        if ($value < 1) {
            throw new InvalidCartValue('Cart item quantity must be greater than 0');
        }
    }

    public static function fromInteger(int $value): self
    {
        return new self($value);
    }

    public function sameValueAs(self $other): bool
    {
        return $this->value === $other->value;
    }
}
