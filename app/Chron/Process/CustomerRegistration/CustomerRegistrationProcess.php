<?php

declare(strict_types=1);

namespace App\Chron\Process\CustomerRegistration;

use App\Chron\Saga\ProcessManager;
use Illuminate\Contracts\Foundation\Application;
use Storm\Contract\Message\Messaging;

final readonly class CustomerRegistrationProcess
{
    private ProcessManager $sagaManager;

    public function __construct(private Application $app)
    {
        $this->sagaManager = new ProcessManager();

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
            $this->app[RegisterCustomerStep::class],
            $this->app[CreateAuthUserStep::class],
            $this->app[CreateOpenCartStep::class],
            $this->app[CreateSendEmailToNewCustomerStep::class],
        ];
    }
}
