<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer;

final readonly class CustomerEmail
{
    public function __construct(public string $value)
    {
    }

    public static function fromString(string $email): self
    {
        return new self($email);
    }

    public function equalsTo(CustomerEmail $email): bool
    {
        return $this->value === $email->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
