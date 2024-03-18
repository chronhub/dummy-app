<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer;

use App\Chron\Model\Customer\Exception\InvalidCustomerValue;
use DateTimeImmutable;
use Exception;

final class Birthday
{
    public const FORMAT = 'Y-m-d';

    private function __construct(
        public string $value,
        public DateTimeImmutable $date
    ) {
    }

    // todo validation
    public static function fromString(string $value): self
    {
        try {
            return new self($value, new DateTimeImmutable($value));
        } catch (Exception $e) {
            throw new InvalidCustomerValue('Invalid birth date format', 0, $e);
        }
    }

    public function age(): int
    {
        return (int) $this->date->diff(new DateTimeImmutable())->format('%y');
    }
}
