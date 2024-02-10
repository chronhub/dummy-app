<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use InvalidArgumentException;

final readonly class Amount
{
    private function __construct(public string $value)
    {
        if ($this->toFloat() <= 0) {
            throw new InvalidArgumentException('Amount must a positive number.');
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
