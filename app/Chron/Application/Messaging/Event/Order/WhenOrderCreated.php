<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Model\Order\Event\OrderModified;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\OrderReadModel;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OrderCreated::class,
)]
final class WhenOrderCreated
{
    public function __construct(private OrderReadModel $readModel)
    {
    }

    public function __invoke(OrderModified $event): void
    {
        $this->readModel->insertOrder(
            $event->orderId()->toString(),
            $event->customerId()->toString(),
            $event->orderStatus()->value
        );
    }
}
