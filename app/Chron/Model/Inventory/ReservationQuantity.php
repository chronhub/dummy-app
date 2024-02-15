<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use InvalidArgumentException;

final class ReservationQuantity
{
    public function __construct(public int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('Reservation quantity must be greater than 0.');
        }
    }

    public function toStock(): Stock
    {
        return Stock::create($this->value);
    }

    public static function create(int $value): self
    {
        return new self($value);
    }

    public function sameValueAs(self $other): bool
    {
        return $this->value === $other->value;
    }
}
