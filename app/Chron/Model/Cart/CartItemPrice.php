<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart;

use App\Chron\Model\Cart\Exception\InvalidCartValue;
use App\Chron\Model\Price;

final readonly class CartItemPrice
{
    public string $value;

    private function __construct(Price $price)
    {
        if (! $price->greaterThanZero()) {
            throw new InvalidCartValue('Cart item price must be greater than 0');
        }

        $this->value = $price->value;
    }

    public static function fromString(string $value): self
    {
        return new self(Price::fromString($value));
    }

    public function toFloat(): float
    {
        return (float) $this->value;
    }

    public function sameValueAs(self $other): bool
    {
        return $this->value === $other->value;
    }
}
