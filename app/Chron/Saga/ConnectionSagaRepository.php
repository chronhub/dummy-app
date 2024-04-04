<?php

declare(strict_types=1);

namespace App\Chron\Saga;

use Illuminate\Database\Connection;
use Storm\Contract\Serializer\StreamEventSerializer;

final readonly class ConnectionSagaRepository implements SagaRepository
{
    public function __construct(
        private Connection $connection,
        private StreamEventSerializer $serializer
    ) {

    }

    public function save(SagaValue $saga): void
    {
        // TODO: Implement save() method.
    }

    public function find(string $sagaId): ?SagaValue
    {
        // TODO: Implement find() method.
    }
}
