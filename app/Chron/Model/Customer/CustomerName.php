<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer;

final readonly class CustomerName
{
    public function __construct(public string $value)
    {
    }

    public static function fromString(string $name): self
    {
        return new self($name);
    }

    public function sameValueAs(CustomerName $email): bool
    {
        return $this->value === $email->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
