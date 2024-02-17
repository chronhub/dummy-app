<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Customer\Exception\InvalidCustomerValue;
use App\Chron\Package\Aggregate\AggregateIdV4Trait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use InvalidArgumentException;

final class OrderId implements AggregateIdentity
{
    use AggregateIdV4Trait;
    use AggregateIdV4Trait {
        fromString as private fromStringV4;
    }

    public static function fromString(string $aggregateId): static
    {
        try {
            return self::fromStringV4($aggregateId);
        } catch (InvalidArgumentException) {
            throw new InvalidCustomerValue('Invalid Order id');
        }
    }
}
