<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Model\Inventory\Exception\InvalidInventoryValue;

final class Quantity
{
    private function __construct(public int $value)
    {
        if ($value < 0) {
            throw new InvalidInventoryValue('Inventory quantity must be greater or equals than 0.');
        }
    }

    public static function create(int $quantity): self
    {
        return new self($quantity);
    }

    public function toPositiveQuantity(): PositiveQuantity
    {
        return PositiveQuantity::create($this->value);
    }

    public function sameValueAs(self $other): bool
    {
        return $this->value === $other->value;
    }
}
