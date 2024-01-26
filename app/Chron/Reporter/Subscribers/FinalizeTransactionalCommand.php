<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use Closure;
use Illuminate\Database\Connection;
use Storm\Contract\Message\Header;
use Storm\Contract\Tracker\MessageStory;
use Storm\Message\Message;

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
                if ($this->inTransaction($message)) {
                    $this->connection->commit();
                    logger('Commit transactional for command: '.$message->name());
                }
            }
        };
    }

    private function inTransaction(Message $message): bool
    {
        $queue = $message->header(Header::QUEUE);

        if ($message->header(Header::EVENT_DISPATCHED) !== true || $this->connection->transactionLevel() === 0) {
            return false;
        }

        // assume sync
        if ($queue === null) {
            return true;
        }

        $queueData = QueueData::fromArray($queue[0]);

        return $queueData->isCompleted();
    }
}
