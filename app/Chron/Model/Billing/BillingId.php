<?php

declare(strict_types=1);

namespace App\Chron\Model\Billing;

use Storm\Aggregate\AggregateIdV4Trait;
use Storm\Contract\Aggregate\AggregateIdentity;

final class BillingId implements AggregateIdentity
{
    use AggregateIdV4Trait;
}
