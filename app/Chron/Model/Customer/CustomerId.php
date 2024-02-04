<?php

declare(strict_types=1);

namespace App\Chron\Model\Customer;

use App\Chron\Aggregate\AggregateIdV4Trait;
use App\Chron\Aggregate\Contract\AggregateIdentity;

final class CustomerId implements AggregateIdentity
{
    use AggregateIdV4Trait;
}
