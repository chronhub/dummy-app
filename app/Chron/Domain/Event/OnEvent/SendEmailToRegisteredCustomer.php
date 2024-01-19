<?php

declare(strict_types=1);

namespace App\Chron\Domain\Event\OnEvent;

use App\Chron\Attribute\AsMessageHandler;
use App\Chron\Domain\Event\CustomerRegistered;

#[AsMessageHandler(
    reporter: 'reporter.event.default',
    handles: CustomerRegistered::class,
    fromQueue: ['connection' => 'redis', 'name' => 'default'],
    method: 'onEvent',
    priority: 3,
)]
final class SendEmailToRegisteredCustomer
{
    public function onEvent(CustomerRegistered $event): void
    {
        logger('Send email to registered customer: '.$event->content['customer_email']);
    }
}
