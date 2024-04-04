<?php

declare(strict_types=1);

namespace App\Chron\Process\CartReservation;

use App\Chron\Saga\SagaManager;
use Illuminate\Contracts\Foundation\Application;
use Storm\Contract\Message\Messaging;

final readonly class AddCartItemSaga
{
    private SagaManager $sagaManager;

    public function __construct(private Application $app)
    {
        $this->sagaManager = new SagaManager();

        foreach ($this->getSteps() as $step) {
            $this->sagaManager->addStep($step);
        }
    }

    public function handle(Messaging $event): void
    {
        $this->sagaManager->handle($event);
    }

    private function getSteps(): array
    {
        return [
            $this->app[ReserveCartItemStep::class],
            $this->app[AddCartItemStep::class],
        ];
    }
}
