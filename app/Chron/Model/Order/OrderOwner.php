<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Customer\Exception\InvalidCustomerValue;
use App\Chron\Package\Aggregate\AggregateIdV4Trait;
use InvalidArgumentException;

final class OrderOwner
{
    use AggregateIdV4Trait {
        fromString as private fromStringV4;
    }

    public static function fromString(string $aggregateId): static
    {
        try {
            return self::fromStringV4($aggregateId);
        } catch (InvalidArgumentException) {
            throw new InvalidCustomerValue('Invalid order owner id');
        }
    }
}