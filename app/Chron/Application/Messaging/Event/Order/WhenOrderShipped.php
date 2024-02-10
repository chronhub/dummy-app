<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\OrderShipped;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OrderShipped::class,
)]
final class WhenOrderShipped
{
    public function __invoke(OrderShipped $event): void
    {
        logger('Order shipped: '.$event->orderId()->toString().' for customer: '.$event->customerId()->toString());
    }
}
