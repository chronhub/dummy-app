<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Projection\ReadModel\ProductReadModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

final readonly class ProductProvider
{
    public function __construct(private Connection $connection)
    {
    }

    public function getPaginatedProducts(int $perPage): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    private function query(): Builder
    {
        return $this->connection->table(ProductReadModel::TABLE);
    }
}
