<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\OrderCanceled;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OrderCanceled::class,
)]
final class WhenOrderCanceled
{
    public function __invoke(OrderCanceled $event): void
    {
        logger('Order canceled: '.$event->orderId()->toString().' for customer: '.$event->customerId()->toString());
    }
}
