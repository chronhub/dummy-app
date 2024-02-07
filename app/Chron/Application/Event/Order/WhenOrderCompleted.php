<?php

declare(strict_types=1);

namespace App\Chron\Application\Event\Order;

use App\Chron\Model\Order\Event\OrderCompleted;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OrderCompleted::class,
)]
final class WhenOrderCompleted
{
    public function __invoke(OrderCompleted $event): void
    {
        logger('Order completed:'.$event->orderId().' for customer: '.$event->customerId());
    }
}
