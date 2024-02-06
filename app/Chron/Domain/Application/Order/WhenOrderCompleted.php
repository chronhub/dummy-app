<?php

declare(strict_types=1);

namespace App\Chron\Domain\Application\Order;

use App\Chron\Attribute\Messaging\AsEventHandler;
use App\Chron\Model\Order\Event\OrderCompleted;

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
