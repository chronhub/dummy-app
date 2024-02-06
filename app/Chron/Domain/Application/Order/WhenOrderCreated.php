<?php

declare(strict_types=1);

namespace App\Chron\Domain\Application\Order;

use App\Chron\Attribute\Messaging\AsEventHandler;
use App\Chron\Model\Order\Event\OrderCreated;

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
