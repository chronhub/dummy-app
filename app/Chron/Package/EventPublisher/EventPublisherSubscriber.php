<?php

declare(strict_types=1);

namespace App\Chron\Package\EventPublisher;

use App\Chron\Package\Chronicler\Contracts\EventableChronicler;
use App\Chron\Package\Chronicler\Contracts\TransactionalChronicler;
use App\Chron\Package\Chronicler\Contracts\TransactionalEventableChronicler;
use Illuminate\Support\Collection;
use Storm\Contract\Tracker\StreamStory;

final readonly class EventPublisherSubscriber
{
    public function __construct(private EventPublisher $eventPublisher)
    {
    }

    public function __invoke(EventableChronicler|TransactionalEventableChronicler $chronicler): void
    {
        $this->onAppendOnlyStream($chronicler);

        if ($chronicler instanceof TransactionalEventableChronicler) {
            $this->onTransactionalStream($chronicler);
        }
    }

    private function onAppendOnlyStream(EventableChronicler $chronicler): void
    {
        $chronicler->subscribe(EventableChronicler::APPEND_STREAM_EVENT, function (StreamStory $story) use ($chronicler): void {
            $streamEvents = new Collection($story->promise()->events());

            if (! $this->inTransaction($chronicler)) {
                if (! $story->hasException()) {
                    $this->eventPublisher->publish(...$streamEvents);
                }
            } else {
                $this->eventPublisher->record($streamEvents);
            }
        });
    }

    private function onTransactionalStream(TransactionalEventableChronicler $chronicler): void
    {
        $chronicler->subscribe(TransactionalEventableChronicler::COMMIT_TRANSACTION_EVENT, function (): void {
            $pendingEvents = $this->eventPublisher->pull();

            $this->eventPublisher->publish(...$pendingEvents);
        });

        $chronicler->subscribe(TransactionalEventableChronicler::ROLLBACK_TRANSACTION_EVENT, function (): void {
            $this->eventPublisher->flush();
        });
    }

    private function inTransaction(EventableChronicler $chronicler): bool
    {
        return $chronicler instanceof TransactionalChronicler && $chronicler->inTransaction();
    }
}
