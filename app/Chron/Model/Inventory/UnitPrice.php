<?php

declare(strict_types=1);

namespace App\Chron\Model\Inventory;

use InvalidArgumentException;

use function number_format;

final readonly class UnitPrice
{
    public string $value;

    private function __construct(string $value)
    {
        $floatUnitPrice = (float) $value;

        if ($floatUnitPrice < 0) {
            throw new InvalidArgumentException('Unit price must be greater than or equal to 0');
        }

        $this->value = number_format($floatUnitPrice, 2, '.', '');
    }

    public static function create(string $unitPrice): self
    {
        return new self($unitPrice);
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
