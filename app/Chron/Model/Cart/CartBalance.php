<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart;

use App\Chron\Model\Cart\Exception\InvalidCartValue;
use App\Chron\Model\Price;

use function number_format;

final readonly class CartBalance
{
    public string $value;

    private function __construct(Price $price)
    {
        if (! $price->greaterOrEqualsThanZero()) {
            throw new InvalidCartValue('Cart item price must be greater or equals than 0');
        }

        $this->value = $price->value;
    }

    public static function fromString(string $value): self
    {
        return new self(Price::fromString($value));
    }

    public static function fromDefault(): self
    {
        return new self(Price::fromZero());
    }

    public function add(CartItemPrice $price, CartItemQuantity $quantity): self
    {
        $sum = ($this->toFloat() + ($price->toFloat() * $quantity->value));

        $format = number_format($sum, 2, '.', '');

        return new self(Price::fromString($format));
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
