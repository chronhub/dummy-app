<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\OrderClosed;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OrderClosed::class,
)]
final class WhenOrderClosed
{
    public function __invoke(OrderClosed $event): void
    {
        logger('Order closed: '.$event->reason());
    }
}
