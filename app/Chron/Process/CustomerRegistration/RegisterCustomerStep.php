<?php

declare(strict_types=1);

namespace App\Chron\Process\CustomerRegistration;

use App\Chron\Model\Customer\Event\CustomerRegistered;
use App\Chron\Saga\ProcessManagerException;
use App\Chron\Saga\ProcessStep;
use Storm\Contract\Message\Messaging;
use Throwable;

use function sprintf;

class RegisterCustomerStep implements ProcessStep
{
    public function shouldHandle(Messaging $event): bool
    {
        return $event instanceof CustomerRegistered;
    }

    public function handle(Messaging $event): void
    {
        if (! $event instanceof CustomerRegistered) {
            throw new ProcessManagerException('Event is not an instance of RegisterCustomer');
        }

        logger(sprintf('Start register new customer %s', $event->aggregateId()->toString()));
    }

    public function compensate(?Throwable $exception): void
    {
        logger('Exception occurred, compensating... with exception: '.$exception->getMessage());
    }
}
