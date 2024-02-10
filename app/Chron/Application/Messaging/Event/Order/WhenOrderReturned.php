<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Order;

use App\Chron\Model\Order\Event\OrderReturned;
use App\Chron\Package\Attribute\Messaging\AsEventHandler;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OrderReturned::class,
)]
final class WhenOrderReturned
{
    public function __invoke(OrderReturned $event): void
    {
        logger('Order returned: '.$event->orderId()->toString().' for customer: '.$event->customerId()->toString());
    }
}
