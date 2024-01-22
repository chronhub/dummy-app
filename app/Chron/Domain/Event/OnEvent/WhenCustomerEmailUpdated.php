<?php

declare(strict_types=1);

namespace App\Chron\Domain\Event\OnEvent;

use App\Chron\Attribute\MessageHandler\AsEventHandler;
use App\Chron\Domain\Event\CustomerEmailUpdated;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: CustomerEmailUpdated::class,
    method: 'onEvent',
)]
final class WhenCustomerEmailUpdated
{
    public function onEvent(CustomerEmailUpdated $event): void
    {
        logger('Customer email updated async');
    }
}
