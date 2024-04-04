<?php

declare(strict_types=1);

namespace App\Chron\Saga;

final readonly class SagaValue
{
    public function __construct(
        public string $sagaId,
        public string $sagaType,
        public string $sagaStatus,
        public string $sagaData,
        public string $sagaCreatedAt,
        public string $sagaUpdatedAt
    ) {

    }
}
