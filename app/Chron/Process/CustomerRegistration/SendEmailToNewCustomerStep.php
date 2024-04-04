<?php

declare(strict_types=1);

namespace App\Chron\Process\CustomerRegistration;

use App\Chron\Model\Customer\Event\CustomerRegistered;
use App\Chron\Saga\ProcessManagerException;
use App\Chron\Saga\SagaStep;
use Storm\Contract\Message\Messaging;
use Throwable;

use function sprintf;

// probably not part of saga
final readonly class SendEmailToNewCustomerStep implements SagaStep
{
    public function shouldHandle(Messaging $event): bool
    {
        return $event instanceof CustomerRegistered;
    }

    public function handle(Messaging $event): void
    {
        $event = $this->getTypedEvent($event);

        logger(sprintf('Send email to new registered customer %s', $event->aggregateId()->toString()));
    }

    public function compensate(Messaging $event, ?Throwable $exception): void
    {

    }

    private function getTypedEvent(Messaging $event): CustomerRegistered
    {
        if (! $event instanceof CustomerRegistered) {
            throw new ProcessManagerException('Event is not an instance of CustomerRegistered');
        }

        return $event;
    }
}
