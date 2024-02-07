<?php

declare(strict_types=1);

namespace App\Chron\Application\Event\Order;

use App\Chron\Model\Order\Event\OrderCreated;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OrderCreated::class,
)]
final class WhenOrderCreated
{
    public function __invoke(OrderCreated $event): void
    {
        logger('Order created:'.$event->orderId()->toString().' for customer: '.$event->customerId()->toString());
    }
}
