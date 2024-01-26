<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use Closure;
use Illuminate\Database\Connection;
use Storm\Contract\Tracker\MessageStory;

final readonly class FinalizeTransactionalCommand
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            $message = $story->message();

            if ($story->hasException()) {
                $this->connection->rollBack();
                logger('Rollback transactional for command: '.$message->name(), ['exception' => $story->exception()->getMessage()]);
            } else {
                if ($this->inTransaction()) {
                    $this->connection->commit();
                    logger('Commit transactional for command: '.$message->name());
                }
            }
        };
    }

    private function inTransaction(): bool
    {
        return $this->connection->transactionLevel() > 0;
    }
}
