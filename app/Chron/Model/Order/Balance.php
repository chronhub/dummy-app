<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use InvalidArgumentException;

final class Balance
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;

        if ($this->toFloat() < 0) {
            throw new InvalidArgumentException('Balance must a positive number.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function newInstance(): self
    {
        return new self('0.00');
    }

    public function add(Amount $amount): void
    {
        $this->value = (string) ((float) $this->value + $amount->toFloat());
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
