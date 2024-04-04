<?php

declare(strict_types=1);

namespace App\Chron\Process\CustomerRegistration;

use App\Chron\Application\Service\AuthApplicationService;
use App\Chron\Model\Customer\Event\CustomerRegistered;
use App\Chron\Saga\ProcessStep;
use Storm\Contract\Message\Messaging;
use Throwable;

use function sprintf;

final readonly class CreateAuthUserStep implements ProcessStep
{
    public function __construct(private AuthApplicationService $authApplicationService)
    {
    }

    public function shouldHandle($event): bool
    {
        return $event instanceof CustomerRegistered;
    }

    public function handle(Messaging $event): void
    {
        if (! $event instanceof CustomerRegistered) {
            return;
        }

        $this->authApplicationService->createAuthUser(
            $event->aggregateId()->toString(),
            $event->name()->value,
            $event->email()->value
        );

        logger(sprintf('Create auth user for new registered customer %s', $event->aggregateId()->toString()));
    }

    public function compensate(?Throwable $exception): void
    {

    }
}
