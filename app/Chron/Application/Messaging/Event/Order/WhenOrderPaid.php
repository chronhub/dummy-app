<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\OrderPaid;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OrderPaid::class,
)]
final class WhenOrderPaid
{
    public function __invoke(OrderPaid $event): void
    {
        logger('Order paid: '.$event->orderId()->toString().' for customer: '.$event->customerId()->toString());
    }
}
