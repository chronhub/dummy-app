<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\OrderDelivered;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OrderDelivered::class,
)]
final class WhenOrderDelivered
{
    public function __invoke(OrderDelivered $event): void
    {
        logger('Order delivered: '.$event->orderId()->toString().' for customer: '.$event->customerId()->toString());
    }
}
