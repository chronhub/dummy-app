<?php

declare(strict_types=1);

namespace App\Chron\Saga;

interface SagaRepository
{
    public function save($saga): void;

    public function find(string $sagaId);
}
