<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\CustomerRequestedOrderCanceled;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;
use App\Chron\Projection\ReadModel\OrderReadModel;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: CustomerRequestedOrderCanceled::class,
)]
final readonly class WhenCustomerRequestedOrderCanceled
{
    public function __construct(private OrderReadModel $readModel)
    {
    }

    public function __invoke(CustomerRequestedOrderCanceled $event): void
    {
        $this->readModel->deleteOrderItem(
            $event->orderId()->toString(),
            $event->customerId()->toString(),
        );
    }
}
