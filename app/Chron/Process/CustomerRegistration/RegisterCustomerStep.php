<?php

declare(strict_types=1);

namespace App\Chron\Process\CustomerRegistration;

use App\Chron\Application\Messaging\Command\Customer\RegisterCustomer;
use App\Chron\Saga\ProcessManagerException;
use App\Chron\Saga\SagaStep;
use Storm\Contract\Message\Messaging;
use Storm\Support\Facade\Report;
use Throwable;

final class RegisterCustomerStep implements SagaStep
{
    public function shouldHandle(Messaging $event): bool
    {
        return $event instanceof RegisterCustomer;
    }

    public function handle(Messaging $event): void
    {
        if (! $event instanceof RegisterCustomer) {
            throw new ProcessManagerException('Event is not an instance of RegisterCustomer');
        }

        Report::relay($event);
    }

    public function compensate(Messaging $event, ?Throwable $exception): void
    {
        logger('Error occurred with exception: '.$exception->getMessage());
    }
}
