<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Order\Exception\InvalidOrderValue;

final readonly class Amount
{
    private function __construct(public string $value)
    {
        if ($this->toFloat() <= 0) {
            throw new InvalidOrderValue('Order amount must a positive number.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toFloat(): float
    {
        return (float) $this->value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
