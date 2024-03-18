<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer;

final class PhoneNumber
{
    private function __construct(public string $value)
    {
    }

    public static function fromString(string $phoneNumber): self
    {
        return new self($phoneNumber);
    }

    public function sameValueAs(self $phoneNumber): bool
    {
        return $this->value === $phoneNumber->value;
    }
}
