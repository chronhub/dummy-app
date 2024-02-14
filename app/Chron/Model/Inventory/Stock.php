<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use InvalidArgumentException;

final readonly class Stock
{
    private function __construct(public int $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Stock must be greater than or equal to 0');
        }
    }

    public static function create(int $stock): self
    {
        return new self($stock);
    }

    public function add(Stock $stock): self
    {
        return new self($this->value + $stock->value);
    }

    public function remove(Stock $stock): self
    {
        return new self($this->value - $stock->value);
    }

    public function isFullyAvailable(Stock $requested): bool
    {
        return $this->value >= $requested->value;
    }

    public function isPartiallyAvailable(Stock $requested): bool
    {
        return $this->value > 0 && $requested->value - $this->value > 0;
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
