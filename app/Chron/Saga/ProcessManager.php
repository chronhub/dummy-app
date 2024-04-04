<?php

declare(strict_types=1);

namespace App\Chron\Saga;

use Exception;
use Illuminate\Support\Collection;
use Throwable;

class ProcessManager
{
    private Collection $steps;

    private Collection $completedSteps;

    private Collection $failedSteps;

    public function __construct()
    {
        $this->steps = new Collection();
        $this->completedSteps = new Collection();
        $this->failedSteps = new Collection();
    }

    public function handle($event): void
    {
        $this->steps->each(function (ProcessStep $step) use ($event) {
            if ($step->shouldHandle($event)) {
                try {
                    $step->handle($event);
                    $this->completedSteps->push($step);
                } catch (Exception $e) {
                    $this->failedSteps->push($step);
                    $this->compensate($e);

                    throw new ProcessManagerException($e->getMessage());
                }
            }
        });
    }

    public function addStep(ProcessStep $step): void
    {
        $this->steps->push($step);
    }

    private function compensate(Throwable $exception): void
    {
        while ($this->completedSteps->isNotEmpty()) {
            /** @var ProcessStep $step */
            $step = $this->completedSteps->pop();

            try {
                $step->compensate($exception);
            } catch (Exception $e) {
                $this->failedSteps->push($step);
                $this->compensate($exception);

                throw new ProcessManagerException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}
