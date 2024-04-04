<?php

declare(strict_types=1);

namespace App\Chron\Saga;

use Exception;
use Illuminate\Support\Collection;
use Storm\Contract\Message\Messaging;
use Throwable;

class SagaManager implements Saga
{
    /**
     * @var Collection<SagaStep>
     */
    private Collection $steps;

    /**
     * @var Collection<SagaStep>
     */
    private Collection $completedSteps;

    /**
     * @var Collection<SagaStep>
     */
    private Collection $failedSteps;

    /**
     * @var array<Exception>
     */
    private array $compensatedExceptions = [];

    public function __construct()
    {
        $this->steps = new Collection();
        $this->completedSteps = new Collection();
        $this->failedSteps = new Collection();
    }

    public function handle($event): void
    {
        $this->steps->each(function (SagaStep $step) use ($event) {
            if ($step->shouldHandle($event)) {
                try {
                    $step->handle($event);
                    $this->completedSteps->push($step);
                } catch (Exception $e) {
                    $this->failedSteps->push($step);
                    $this->compensate($event, $e);

                    throw new ProcessManagerException($e->getMessage());
                }
            }
        });
    }

    public function addStep(SagaStep $step): void
    {
        $this->steps->push($step);
    }

    private function compensate(Messaging $event, Throwable $exception): void
    {
        while ($this->completedSteps->isNotEmpty()) {
            /** @var SagaStep $step */
            $step = $this->completedSteps->pop();

            try {
                $step->compensate($event, $exception);
            } catch (Exception $e) {
                $this->failedSteps->push($step);
                $this->compensatedExceptions[] = $e;
            }
        }

        if (! $this->compensatedExceptions == []) {
            throw new ProcessManagerException('Failed to compensate for the following exceptions');
        }
    }
}
