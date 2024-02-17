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

    public function add(Quantity $quantity): self
    {
        return new self($this->value + $quantity->value);
    }

    public function remove(Quantity $quantity): self
    {
        return new self($this->value - $quantity->value);
    }

    public function isOutOfStock(): bool
    {
        return $this->value === 0;
    }

    public function getAvailableQuantity(Quantity $requested): false|Quantity
    {
        if ($this->isOutOfStock()) {
            return false;
        }

        // return the minimum value between the requested quantity and the available stock
        if ($this->value < $requested->value) {
            return Quantity::create($this->value);
        }

        // return the requested quantity
        return Quantity::create($requested->value);
    }

    public function sameValueAs(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toArray(): array
    {
        return ['stock' => $this->value];
    }
}
