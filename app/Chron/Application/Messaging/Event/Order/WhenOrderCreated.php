<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\OrderReadModel;

final readonly class WhenOrderCreated
{
    public function __construct(private OrderReadModel $readModel)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: OrderCreated::class,
        priority: 0
    )]
    public function insertOrder(OrderCreated $event): void
    {
        $this->readModel->insertOrder(
            $event->aggregateId()->toString(),
            $event->orderOwner()->toString(),
            $event->orderStatus()->value,
            $event->orderBalance()->value(),
            $event->orderQuantity()->value
        );
    }

    #[AsEventHandler(
        reporter: 'reporter.event.default',
        handles: OrderCreated::class,
        priority: 1
    )]
    public function insertOrderItems(OrderCreated $event): void
    {
        $this->readModel->insertOrderItems(
            $event->aggregateId()->toString(),
            $event->orderOwner()->toString(),
            $event->orderItems()->toArray()
        );
    }
}
