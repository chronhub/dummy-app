<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use App\Chron\Model\Inventory\Exception\InvalidInventoryValue;

final readonly class Reservation
{
    private function __construct(public int $value)
    {
        if ($value < 0) {
            throw new InvalidInventoryValue('Inventory reservation must be greater than or equal to 0');
        }
    }

    public static function create(int $value): self
    {
        return new self($value);
    }

    public function add(self $reservation): self
    {
        return new self($this->value + $reservation->value);
    }

    public function sub(self $reservation): self
    {
        return new self($this->value - $reservation->value);
    }

    public function remove(self $reservation): self
    {
        return new self($this->value - $reservation->value);
    }

    public function toQuantity(): Quantity
    {
        return Quantity::create($this->value);
    }
}
