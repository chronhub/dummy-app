<?php

declare(strict_types=1);

namespace App\Chron\Model\Order;

use App\Chron\Application\Messaging\Command\Order\CompleteOrder;
use App\Chron\Application\Messaging\Query\QueryRandomPendingOrder;
use App\Chron\Package\Reporter\Report;
use Storm\Support\QueryPromiseTrait;

class OrderService
{
    use QueryPromiseTrait;

    public function completeOrder(): void
    {
        $order = $this->findRandomPendingOrder();

        if ($order === null) {
            return;
        }

        $command = CompleteOrder::forCustomer($order['order_id'], $order['customer_id']);

        Report::relay($command);
    }

    protected function findRandomPendingOrder(): ?array
    {
        $promise = Report::relay(new QueryRandomPendingOrder());

        return $this->handlePromise($promise);
    }
}
