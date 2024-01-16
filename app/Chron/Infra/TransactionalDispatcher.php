<?php

declare(strict_types=1);

namespace App\Chron\Infra;

use Illuminate\Database\Connection;

class TransactionalDispatcher
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function insertData(array $data): void
    {
        $this->connection->transaction(fn () => $this->connection->table('stream_event')->insert($data));
    }
}
