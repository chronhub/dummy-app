<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart;

use App\Chron\Model\Cart\Exception\InvalidCartValue;

use function number_format;

final readonly class CartItemPrice
{
    public string $value;

    private function __construct(string $value)
    {
        $floatPrice = (float) $value;

        if ($floatPrice < 0) {
            throw new InvalidCartValue('Cart item price must be greater than 0');
        }

        $this->value = number_format($floatPrice, 2, '.', '');
    }

    public static function fromString(string $value): self
    {
        return new self($value);
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
