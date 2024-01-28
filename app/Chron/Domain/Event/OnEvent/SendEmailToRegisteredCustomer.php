<?php

declare(strict_types=1);

namespace App\Chron\Domain\Event\OnEvent;

use App\Chron\Attribute\Messaging\AsEventHandler;
use App\Chron\Domain\Event\CustomerRegistered;

#[AsEventHandler(
    reporter: 'reporter.event.notification',
    handles: CustomerRegistered::class,
    fromQueue: ['connection' => 'rabbitmq', 'name' => 'default'],
    method: 'onEvent',
    priority: 1,
)]
final class SendEmailToRegisteredCustomer
{
    public function onEvent(CustomerRegistered $event): void
    {
        logger('Send email to registered customer: '.$event->content['customer_email']);
    }
}
