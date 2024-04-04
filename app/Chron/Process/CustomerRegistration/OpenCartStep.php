<?php

declare(strict_types=1);

namespace App\Chron\Process\CustomerRegistration;

use App\Chron\Application\Service\CartApplicationService;
use App\Chron\Model\Customer\Event\CustomerRegistered;
use App\Chron\Saga\ProcessManagerException;
use App\Chron\Saga\SagaStep;
use Storm\Contract\Message\Messaging;
use Throwable;

use function sprintf;

final readonly class OpenCartStep implements SagaStep
{
    public function __construct(private CartApplicationService $cartApplicationService)
    {
    }

    public function shouldHandle(Messaging $event): bool
    {
        return $event instanceof CustomerRegistered;
    }

    public function handle(Messaging $event): void
    {
        if (! $event instanceof CustomerRegistered) {
            throw new ProcessManagerException('Event is not an instance of CustomerRegistered');
        }

        $this->cartApplicationService->openCart($event->aggregateId()->toString());

        logger(sprintf('Open cart for new registered customer %s', $event->aggregateId()->toString()));
    }

    public function compensate(Messaging $event, ?Throwable $exception): void
    {

    }
}
