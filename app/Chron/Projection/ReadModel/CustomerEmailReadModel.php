<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

final readonly class CustomerEmailReadModel
{
    final public const string TABLE = 'read_customer_email';

    public function __construct(private Connection $connection)
    {
    }

    public function insert(string $customerId, string $email): void
    {
        $this->query()->insert([
            'customer_id' => $customerId,
            'email' => $email,
        ]);
    }

    private function query(): Builder
    {
        return $this->connection->table(self::TABLE);
    }
}
