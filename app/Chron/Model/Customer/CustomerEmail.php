<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer;

use App\Chron\Model\Customer\Exception\InvalidCustomerValue;

use function filter_var;

final readonly class CustomerEmail
{
    private function __construct(public string $value)
    {
    }

    public static function fromString(string $email): self
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidCustomerValue("Invalid customer email address: $email");
        }

        return new self($email);
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
