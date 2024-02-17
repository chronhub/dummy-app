<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer;

use App\Chron\Model\Customer\Exception\InvalidCustomerValue;

final readonly class CustomerName
{
    private function __construct(public string $value)
    {
    }

    public static function fromString(string $name): self
    {
        if (blank($name)) {
            throw new InvalidCustomerValue('Invalid customer name: cannot be empty.');
        }

        return new self($name);
    }

    public function sameValueAs(self $email): bool
    {
        return $this->value === $email->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
