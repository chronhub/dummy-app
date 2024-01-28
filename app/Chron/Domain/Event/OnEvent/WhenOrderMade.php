<?php

declare(strict_types=1);

namespace App\Chron\Domain\Event\OnEvent;

use App\Chron\Attribute\Messaging\AsEventHandler;
use App\Chron\Domain\Event\OrderMade;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: OrderMade::class,
    method: 'onEvent',
)]
final class WhenOrderMade
{
    public function onEvent(OrderMade $event): void
    {
        logger('Order made');
    }
}
