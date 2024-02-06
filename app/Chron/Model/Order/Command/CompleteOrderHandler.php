<?php

declare(strict_types=1);

namespace App\Chron\Model\Order\Command;

use App\Chron\Attribute\Messaging\AsCommandHandler;
use App\Chron\Model\Order\OrderId;
use App\Chron\Model\Order\Repository\OrderList;

#[AsCommandHandler(
    reporter: 'reporter.command.default',
    handles: CompleteOrder::class,
)]
final readonly class CompleteOrderHandler
{
    public function __construct(private OrderList $orders)
    {
    }

    public function __invoke(CompleteOrder $command): void
    {
        $orderId = OrderId::fromString($command->content['order_id']);

        $order = $this->orders->get($orderId);

        $order->complete();

        $this->orders->save($order);
    }
}
