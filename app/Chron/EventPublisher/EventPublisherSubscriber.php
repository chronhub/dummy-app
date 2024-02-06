<?php

declare(strict_types=1);

namespace App\Chron\EventPublisher;

use App\Chron\Chronicler\Contracts\EventableChronicler;
use App\Chron\Chronicler\Contracts\TransactionalChronicler;
use App\Chron\Chronicler\Contracts\TransactionalEventableChronicler;
use Illuminate\Support\Collection;
use Storm\Contract\Tracker\StreamStory;

final readonly class EventPublisherSubscriber
{
    public function __construct(private EventPublisher $eventPublisher)
    {
    }

    public function __invoke(EventableChronicler|TransactionalEventableChronicler $chronicler): void
    {
        $this->subscribeOnAppendOnlyStream($chronicler);

        if ($chronicler instanceof TransactionalEventableChronicler) {
            $this->subscribeOnTransactionalStream($chronicler);
        }
    }

    private function subscribeOnAppendOnlyStream(EventableChronicler $chronicler): void
    {
        $chronicler->subscribe(EventableChronicler::APPEND_STREAM_EVENT, function (StreamStory $story) use ($chronicler): void {
            $streamEvents = new Collection($story->promise()->events());

            if (! $this->inTransaction($chronicler)) {
                logger('publish: not in transaction');
                if (! $story->hasException()) {
                    $this->eventPublisher->publish(...$streamEvents);
                }
            } else {
                logger('record: in transaction');
                $this->eventPublisher->record($streamEvents);
            }
        });
    }

    private function subscribeOnTransactionalStream(TransactionalEventableChronicler $chronicler): void
    {
        $chronicler->subscribe(TransactionalEventableChronicler::COMMIT_TRANSACTION_EVENT, function (): void {
            $pendingEvents = $this->eventPublisher->pull();

            $this->eventPublisher->publish(...$pendingEvents);
            logger('commit');

        });

        $chronicler->subscribe(TransactionalEventableChronicler::ROLLBACK_TRANSACTION_EVENT, function (): void {
            logger('rollback: flush');

            $this->eventPublisher->flush();
        });
    }

    private function inTransaction(EventableChronicler $chronicler): bool
    {
        return $chronicler instanceof TransactionalChronicler && $chronicler->inTransaction();
    }
}
