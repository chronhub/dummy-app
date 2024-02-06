<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Command;

use App\Chron\Attribute\Messaging\AsCommandHandler;
use App\Chron\Model\Customer\CustomerId;
use App\Chron\Model\Order\Order;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\Repository\OrderList;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: CreateOrder::class,
)]
final readonly class CreateOrderHandler
{
    public function __construct(private OrderList $orders)
    {
    }

    public function __invoke(CreateOrder $command): void
    {
        $orderId = OrderId::fromString($command->content['order_id']);
        $customerId = CustomerId::fromString($command->content['customer_id']);

        $order = Order::create($orderId, $customerId);

        $this->orders->save($order);
    }
}
