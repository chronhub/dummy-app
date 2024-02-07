<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Command;

use App\Chron\Application\Command\Order\CompleteOrder;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\Exception\CustomerNotFound;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use App\Chron\Model\Order\Exception\OrderNotFound;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\Repository\OrderList;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: CompleteOrder::class,
)]
final readonly class CompleteOrderHandler
{
    public function __construct(
        private OrderList $orders,
        private CustomerCollection $customers,
    ) {
    }

    public function __invoke(CompleteOrder $command): void
    {
        $customerId = CustomerId::fromString($command->content['customer_id']);

        if ($this->customers->get($customerId) === null) {
            throw CustomerNotFound::withId($customerId);
        }

        $orderId = OrderId::fromString($command->content['order_id']);

        if ($this->orders->get($orderId) === null) {
            throw OrderNotFound::withId($orderId);
        }

        $order = $this->orders->get($orderId);

        $order->complete();

        $this->orders->save($order);
    }
}
