<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Query;

use App\Chron\Model\Order\OrderOwner;

final readonly class QueryOpenOrderOfCustomer
{
    public function __construct(public string $orderOwner)
    {
    }

    public function orderOwner(): OrderOwner
    {
        return OrderOwner::fromString($this->orderOwner);
    }
}
