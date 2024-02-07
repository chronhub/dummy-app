<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Package\Aggregate\AggregateIdV4Trait;
use App\Chron\Package\Aggregate\Contract\AggregateIdentity;

final class OrderId implements AggregateIdentity
{
    use AggregateIdV4Trait;
}
