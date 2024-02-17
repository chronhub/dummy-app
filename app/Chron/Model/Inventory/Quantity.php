<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use InvalidArgumentException;

final class Quantity
{
    private function __construct(public int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('Quantity must be greater than 0.');
        }
    }

    public static function create(int $quantity): self
    {
        return new self($quantity);
    }

    public function sameValueAs(self $other): bool
    {
        return $this->value === $other->value;
    }
}
