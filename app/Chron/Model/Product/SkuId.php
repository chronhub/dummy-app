<?php

declare(strict_types=1);

namespace App\Chron\Model\Product;

use App\Chron\Package\Aggregate\AggregateIdV4Trait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use Symfony\Component\Uid\Uuid;

final class SkuId implements AggregateIdentity
{
    use AggregateIdV4Trait;

    public static function create(): static
    {
        return new self(Uuid::v4());
    }
}
