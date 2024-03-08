<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Model\Inventory\Exception\InvalidInventoryValue;
use App\Chron\Model\Price;

final readonly class UnitPrice
{
    public string $value;

    private function __construct(Price $price)
    {
        if (! $price->greaterThanZero()) {
            throw new InvalidInventoryValue('Inventory unit price must be greater than 0');
        }

        $this->value = $price->value;
    }

    public static function create(string $unitPrice): self
    {
        return new self(Price::fromString($unitPrice));
    }

    public function sameValueAs(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toArray(): array
    {
        return ['unit_price' => $this->value];
    }
}
