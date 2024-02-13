<?php

declare(strict_types=1);

namespace App\Chron\Projection\Provider;

use App\Chron\Model\Customer\CustomerEmail;
use App\Chron\Projection\ReadModel\CustomerEmailReadModel;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

final readonly class CustomerEmailProvider
{
    public function __construct(private Connection $connection)
    {
    }

    public function isUnique(CustomerEmail $email): bool
    {
        return $this->query()->where('email', $email->value)->doesntExist();
    }

    private function query(): Builder
    {
        return $this->connection->table(CustomerEmailReadModel::TABLE);
    }
}
