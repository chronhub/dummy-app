<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use App\Chron\Attribute\Subscriber\AsReporterSubscriber;
use App\Chron\Reporter\DomainType;
use Illuminate\Database\Connection;
use Storm\Contract\Message\Header;
use Storm\Contract\Reporter\Reporter;
use Storm\Contract\Tracker\Listener;
use Storm\Contract\Tracker\MessageStory;
use Storm\Message\Message;
use Storm\Tracker\GenericListener;

final readonly class TransactionalCommand
{
    public function __construct(private Connection $connection)
    {
    }

    #[AsReporterSubscriber(
        supports: ['reporter.command.default'],
        supportType: DomainType::COMMAND,
        method: 'startTransaction',
        autowire: true
    )]
    public function startTransaction(): Listener
    {
        return new GenericListener(Reporter::DISPATCH_EVENT, function (MessageStory $story): void {
            $message = $story->message();

            if ($this->isTransactional($message)) {
                $this->connection->beginTransaction();
                logger('Start transactional for command: '.$message->name());
            } else {
                logger('No transactional for command: '.$message->name());
            }
        }, 10000);
    }

    #[AsReporterSubscriber(
        supports: ['reporter.command.default'],
        supportType: DomainType::COMMAND,
        method: 'finalizeTransaction',
        autowire: true
    )]
    public function finalizeTransaction(): Listener
    {
        return new GenericListener(Reporter::DISPATCH_EVENT, function (MessageStory $story): void {
            $message = $story->message();

            if ($story->hasException()) {
                $this->connection->rollBack();
                logger('Rollback transactional for command: '.$message->name(), ['exception' => $story->exception()->getMessage()]);
            } else {
                if ($this->connection->transactionLevel() > 0) {
                    $this->connection->commit();
                    logger('Commit transactional for command: '.$message->name());
                }
            }
        }, 1000);
    }

    private function isTransactional(Message $message): bool
    {
        $queue = $message->header(Header::QUEUE);

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
