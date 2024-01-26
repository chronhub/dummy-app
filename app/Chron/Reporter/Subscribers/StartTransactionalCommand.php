<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use Closure;
use Illuminate\Database\Connection;
use Storm\Contract\Message\Header;
use Storm\Contract\Tracker\MessageStory;
use Storm\Message\Message;

final readonly class StartTransactionalCommand
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(): Closure
    {
        return function (MessageStory $story): void {
            $message = $story->message();

            if ($this->isTransactional($message)) {
                $this->connection->beginTransaction();
                logger('Start transactional for command: '.$message->name());
            } else {
                logger('No transactional for command: '.$message->name());
            }
        };
    }

    private function isTransactional(Message $message): bool
    {
        $queue = $message->header(Header::QUEUE);

        // todo can known like this if is sync as queue can only bet set after route message
        //  we should try start transaction after route message
        if ($message->header(Header::EVENT_DISPATCHED) !== true) {
            return false;
        }

        // assume sync
        if ($queue === null) {
            logger('transaction with null queue');

            return true;
        }

        logger('transaction with queue');

        $queueData = QueueData::fromArray($queue[0]);

        return $queueData->dispatched === true && $queueData->handled === false;
    }
}
