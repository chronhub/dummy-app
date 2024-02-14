<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Handler;

use App\Chron\Application\Messaging\Command\Order\CreateOrder;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Customer\Exception\CustomerNotFound;
use App\Chron\Model\Customer\Repository\CustomerCollection;
use App\Chron\Model\Order\Exception\OrderAlreadyExists;
use App\Chron\Model\Order\Order;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\Repository\OrderList;
use App\Chron\Package\Attribute\Messaging\AsCommandHandler;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: CreateOrder::class,
)]
final readonly class CreateOrderHandler
{
    public function __construct(
        private OrderList $orders,
        private CustomerCollection $customers,
    ) {
    }

    public function __invoke(CreateOrder $command): void
    {
        $customerId = CustomerId::fromString($command->content['customer_id']);

        if ($this->customers->get($customerId) === null) {
            throw CustomerNotFound::withId($customerId);
        }

        $orderId = OrderId::fromString($command->content['order_id']);

        if ($this->orders->get($orderId) !== null) {
            throw OrderAlreadyExists::withId($orderId);
        }

        $order = Order::create($orderId, $customerId);

        $this->orders->save($order);
    }
}
