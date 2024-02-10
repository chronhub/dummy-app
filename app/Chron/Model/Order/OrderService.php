<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Application\Messaging\Query\QueryRandomPendingOrder;
use App\Chron\Package\Reporter\Report;
use Storm\Support\QueryPromiseTrait;

class OrderService
{
    use QueryPromiseTrait;

    protected function findRandomPendingOrder(): ?array
    {
        $promise = Report::relay(new QueryRandomPendingOrder());

        return $this->handlePromise($promise);
    }
}
