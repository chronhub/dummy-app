<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Model\Inventory\Exception\InvalidInventoryValue;

final readonly class Stock
{
    private function __construct(public int $value)
    {
        if ($value < 0) {
            throw new InvalidInventoryValue('Inventory stock must be greater than or equal to 0');
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

    public function toArray(): array
    {
        return ['inventory_stock' => $this->value];
    }
}
