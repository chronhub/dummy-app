<?php

declare(strict_types=1);

namespace App\Chron\Chronicler;

use Throwable;

trait TransactionalStoreTrait
{
    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commitTransaction(): void
    {
        $this->connection->commit();
    }

    public function rollbackTransaction(): void
    {
        $this->connection->rollBack();
    }

    public function transactional(callable $callback): bool|array|string|int|float|object
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);

            $this->commitTransaction();

            return $result;
        } catch (Throwable $exception) {
            $this->rollbackTransaction();

            throw $exception;
        }
    }

    public function inTransaction(): bool
    {
        return $this->connection->transactionLevel() > 0;
    }
}
