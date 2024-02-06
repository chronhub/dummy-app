<?php

declare(strict_types=1);

namespace App\Chron\Domain\Application\Customer;

use App\Chron\Attribute\Messaging\AsEventHandler;
use App\Chron\Model\Customer\Event\CustomerRegistered;

#[AsEventHandler(
    reporter: 'reporter.event.default',
    handles: CustomerRegistered::class,
    priority: 1
)]
class SendEmailToRegisteredCustomer
{
    public function __invoke(CustomerRegistered $event): void
    {
        logger('SendEmailToRegisteredCustomer');
    }
}
