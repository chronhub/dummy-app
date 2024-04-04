<?php

declare(strict_types=1);

namespace App\Chron\Saga;

interface Saga
{
    public function handle($event): void;

    public function addStep(SagaStep $step): void;
}
