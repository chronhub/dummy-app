<?php

declare(strict_types=1);

namespace App\Chron\Domain\Event\OnEvent;

use App\Chron\Attribute\MessageHandler\AsMessageHandler;
use App\Chron\Domain\Event\CustomerEmailUpdated;

#[AsMessageHandler(
    reporter: 'reporter.event.default',
    handles: CustomerEmailUpdated::class,
    method: 'onEvent',
    priority: 0,
)]
final class WhenCustomerEmailUpdated
{
    public function onEvent(CustomerEmailUpdated $event): void
    {
        logger('Customer email updated');
    }
}
