<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart;

use App\Chron\Model\Cart\Exception\InvalidCartValue;

final readonly class CartQuantity
{
    private function __construct(public int $value)
    {
        if ($value < 0) {
            throw new InvalidCartValue('Cart quantity must be greater than or equal to 0');
        }
    }

    public static function fromInteger(int $value): self
    {
        return new self($value);
    }

    public static function fromDefault(): self
    {
        return new self(0);
    }

    public function isEmpty(): bool
    {
        return $this->value === 0;
    }

    public function sameValueAs(self $other): bool
    {
        return $this->value === $other->value;
    }
}
