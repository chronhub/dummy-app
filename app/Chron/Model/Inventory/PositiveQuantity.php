<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Model\Inventory\Exception\InvalidInventoryValue;

final readonly class PositiveQuantity
{
    private function __construct(public int $value)
    {
        if ($value < 1) {
            throw new InvalidInventoryValue('Inventory quantity must be greater than 0.');
        }
    }

    public static function create(int $quantity): self
    {
        return new self($quantity);
    }

    public function toQuantity(): Quantity
    {
        return Quantity::create($this->value);
    }

    public function sameValueAs(self $other): bool
    {
        return $this->value === $other->value;
    }
}
