<?php

declare(strict_types=1);

namespace App\Chron\Package\EventPublisher;

use App\Chron\Package\Attribute\Reference\Reference;
use App\Chron\Package\Attribute\StreamSubscriber\AsStreamSubscriber;
use App\Chron\Package\Chronicler\Contracts\Chronicler;
use App\Chron\Package\Chronicler\Contracts\EventableChronicler;
use App\Chron\Package\Chronicler\Contracts\TransactionalChronicler;
use App\Chron\Package\Chronicler\Contracts\TransactionalEventableChronicler;
use Closure;
use Illuminate\Support\Collection;
use Storm\Contract\Tracker\StreamStory;

final readonly class EventPublisherSubscriber
{
    public function __construct(#[Reference('event.publisher.in_memory')] private EventPublisher $eventPublisher)
    {
    }

    #[AsStreamSubscriber(
        event: EventableChronicler::APPEND_STREAM_EVENT,
        chronicler: 'chronicler.event.*',
        priority: 100
    )]
    public function onAppendOnlyStream(Chronicler $chronicler): Closure
    {
        return function (StreamStory $story) use ($chronicler): void {
            $streamEvents = new Collection($story->promise()->events());

            if (! $this->inTransaction($chronicler)) {
                if (! $story->hasException()) {
                    $this->eventPublisher->publish(...$streamEvents);
                }
            } else {
                $this->eventPublisher->record($streamEvents);
            }
        };
    }

    #[AsStreamSubscriber(
        event: TransactionalEventableChronicler::COMMIT_TRANSACTION_EVENT,
        chronicler: 'chronicler.event.transactional.*'
    )]
    public function onCommitStream(): Closure
    {
        return function (): void {
            $pendingEvents = $this->eventPublisher->pull();

            $this->eventPublisher->publish(...$pendingEvents);
        };
    }

    #[AsStreamSubscriber(
        event: TransactionalEventableChronicler::ROLLBACK_TRANSACTION_EVENT,
        chronicler: 'chronicler.event.transactional.*'
    )]
    public function onRollbackStream(): Closure
    {
        return function (): void {
            $this->eventPublisher->flush();
        };
    }

    private function inTransaction(Chronicler $chronicler): bool
    {
        return $chronicler instanceof TransactionalChronicler && $chronicler->inTransaction();
    }
}
