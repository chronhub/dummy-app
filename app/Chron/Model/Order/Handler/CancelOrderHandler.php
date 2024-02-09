<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Command\Order\CancelOrder;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\Exception\CustomerNotFound;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use App\Chron\Model\Order\Exception\OrderNotFound;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\Repository\OrderList;

final readonly class CancelOrderHandler
{
    public function __construct(
        private OrderList $orders,
        private CustomerCollection $customers,
    ) {
    }

    public function __invoke(CancelOrder $command): void
    {
        $customerId = CustomerId::fromString($command->content['customer_id']);

        if ($this->customers->get($customerId) === null) {
            throw CustomerNotFound::withId($customerId);
        }

        $orderId = OrderId::fromString($command->content['order_id']);

        $order = $this->orders->get($orderId);

        if ($order === null) {
            throw OrderNotFound::withId($orderId);
        }

        $order->cancel();

        $this->orders->save($order);
    }
}
