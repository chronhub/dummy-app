<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Order\Exception\InvalidOrderValue;

use function number_format;

final class Balance
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $this->formatValue($value);

        if ($this->toFloat() < 0) {
            throw new InvalidOrderValue('Order balance must a positive number.');
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
        $this->value = number_format($this->toFloat() + $amount->toFloat(), 2, '.', '');
    }

    public function toFloat(): float
    {
        return (float) $this->value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function sameValueAs(Balance $balance): bool
    {
        return $balance instanceof $this && $this->value === $balance->value();
    }

    private function formatValue(string $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
