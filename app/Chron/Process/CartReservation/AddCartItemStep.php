<?php

declare(strict_types=1);

namespace App\Chron\Process\CartReservation;

use App\Chron\Application\Messaging\Command\Cart\AddCartItem;
use App\Chron\Saga\SagaStep;
use Storm\Contract\Message\Messaging;
use Storm\Support\Facade\Report;
use Throwable;

final class AddCartItemStep implements SagaStep
{
    public function shouldHandle(Messaging $event): bool
    {
        return $event instanceof AddCartItem;
    }

    public function handle(Messaging $event): void
    {
        if (! $event instanceof AddCartItem) {
            return;
        }

        Report::relay($event);
    }

    public function compensate(Messaging $event, ?Throwable $exception): void
    {
        logger()->error('Item addition failed', [
            'exception' => $exception?->getMessage() ?? 'Unknown error',
        ]);
    }
}
