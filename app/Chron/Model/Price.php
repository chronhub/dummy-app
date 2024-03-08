<?php

declare(strict_types=1);

namespace App\Chron\Model;

use function preg_match;

// todo move from string to integer
final readonly class Price
{
    private function __construct(public string $value)
    {
    }

    /**
     * Price disallow negative and integer values
     * Price must exactly have two decimals
     *
     * @throws InvalidPriceValue when the value is not a positive number with two decimals
     */
    public static function fromString(string $value): self
    {
        if (! preg_match('/^\d+\.\d{2}$/', $value)) {
            throw new InvalidPriceValue('Price must be a number with two decimals');
        }

        return new self($value);
    }

    public static function fromZero(): self
    {
        return new self('0.00');
    }

    public function toFloat(): float
    {
        return (float) $this->value;
    }

    public function greaterThanZero(): bool
    {
        return (float) $this->value > 0;
    }

    public function greaterOrEqualsThanZero(): bool
    {
        return (float) $this->value >= 0;
    }
}
