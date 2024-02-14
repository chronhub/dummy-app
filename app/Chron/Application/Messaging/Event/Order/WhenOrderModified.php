<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\OrderModified;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\OrderReadModel;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OrderModified::class,
)]
final readonly class WhenOrderModified
{
    public function __construct(private OrderReadModel $readModel)
    {

    }

    public function __invoke(OrderModified $event): void
    {
        $this->readModel->updateOrder(
            $event->orderId()->toString(),
            $event->customerId()->toString(),
            $event->balance()->value(),
            $event->quantity()->value,
            $event->orderStatus()->value
        );
    }
}
