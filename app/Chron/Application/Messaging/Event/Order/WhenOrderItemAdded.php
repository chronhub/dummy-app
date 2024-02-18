<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\OrderItemAdded;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\OrderReadModel;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OrderItemAdded::class,
)]
final readonly class WhenOrderItemAdded
{
    public function __construct(private OrderReadModel $readModel)
    {
    }

    public function __invoke(OrderItemAdded $event): void
    {
        $orderItem = $event->orderItem();

        $this->readModel->insertOrderItem(
            $event->aggregateId()->toString(),
            $event->orderId()->toString(),
            $event->orderOwner()->toString(),
            $orderItem->skuId->toString(),
            $orderItem->quantity->value,
            $orderItem->unitPrice->value,
        );
    }
}
