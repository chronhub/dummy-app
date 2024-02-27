<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart;

use App\Chron\Model\Cart\Exception\InvalidCartValue;
use App\Chron\Package\Aggregate\AggregateIdV4Trait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

final class CartItemId implements AggregateIdentity
{
    use AggregateIdV4Trait{
        fromString as private fromStringV4;
    }

    public static function create(): self
    {
        return new self(Uuid::v4());
    }

    public static function fromString(string $aggregateId): static
    {
        try {
            return self::fromStringV4($aggregateId);
        } catch (InvalidArgumentException) {
            throw new InvalidCartValue('Invalid Cart item id');
        }
    }
}
