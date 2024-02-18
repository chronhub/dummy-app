<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\OwnerRequestedOrderCanceled;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\OrderReadModel;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OwnerRequestedOrderCanceled::class,
)]
final readonly class WhenOwnerRequestedOrderCanceled
{
    public function __construct(private OrderReadModel $readModel)
    {
    }

    public function __invoke(OwnerRequestedOrderCanceled $event): void
    {
        $this->readModel->deleteOrderItem(
            $event->aggregateId()->toString(),
            $event->orderOwner()->toString(),
        );
    }
}
