<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use InvalidArgumentException;

final readonly class Quantity
{
    private function __construct(public int $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Quantity must be greater than or equal to 0');
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
        return ['quantity' => $this->value];
    }
}
