<?php

declare(strict_types=1);

namespace App\Chron\Saga;

interface SagaRepository
{
    public function save(SagaValue $saga): void;

    public function find(string $sagaId): ?SagaValue;
}
