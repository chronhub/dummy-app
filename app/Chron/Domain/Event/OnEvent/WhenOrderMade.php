<?php

declare(strict_types=1);

namespace App\Chron\Domain\Event\OnEvent;

use App\Chron\Attribute\MessageHandler\AsMessageHandler;
use App\Chron\Domain\Event\OrderMade;

#[AsMessageHandler(
    reporter: 'reporter.event.default',
    handles: OrderMade::class,
    method: 'onEvent',
    priority: 0,
)]
final class WhenOrderMade
{
    public function onEvent(OrderMade $event): void
    {
        logger('Order made');
    }
}
