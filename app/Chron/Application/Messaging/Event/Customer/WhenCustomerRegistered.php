<?php

declare(strict_types=1);

namespace App\Chron\Application\Messaging\Event\Customer;

use App\Chron\Model\Customer\Event\CustomerRegistered;
use App\Chron\Process\CustomerRegistration\CustomerRegistrationProcess;
use Storm\Message\Attribute\AsEventHandler;

final readonly class WhenCustomerRegistered
{
    public function __construct(private CustomerRegistrationProcess $saga)
    {
    }

    #[AsEventHandler(
        reporter: 'reporter.event.sync.default',
        handles: CustomerRegistered::class,
        priority: 0
    )]
    public function toSaga(CustomerRegistered $event): void
    {
        $this->saga->handle($event);
    }
}
