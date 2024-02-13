<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Projection\ReadModel\InventoryReadModel;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use stdClass;

final readonly class InventoryProvider
{
    public function __construct(private Connection $connection)
    {
    }

    public function findRandomInventoryItem(): ?stdClass
    {
        return $this->query()->inRandomOrder()->first();
    }

    private function query(): Builder
    {
        return $this->connection->table(InventoryReadModel::TABLE);
    }
}
