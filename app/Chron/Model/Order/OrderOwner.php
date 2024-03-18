<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Model\Customer\Exception\InvalidCustomerValue;
use InvalidArgumentException;
use Storm\Aggregate\AggregateIdV4Trait;
use Storm\Contract\Aggregate\AggregateIdentity;

final class OrderOwner implements AggregateIdentity
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
