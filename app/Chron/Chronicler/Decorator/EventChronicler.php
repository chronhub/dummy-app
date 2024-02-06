<?php

declare(strict_types=1);

namespace App\Chron\Chronicler\Decorator;

use App\Chron\Aggregate\Contract\AggregateIdentity;
use App\Chron\Chronicler\Contracts\Chronicler;
use App\Chron\Chronicler\Contracts\EventableChronicler;
use App\Chron\Chronicler\Contracts\QueryFilter;
use App\Chron\Chronicler\Contracts\TransactionalChronicler;
use App\Chron\Chronicler\Direction;
use App\Chron\Chronicler\ProvideEvents;
use App\Chron\Chronicler\StreamListener;
use Generator;
use Storm\Contract\Tracker\Listener;
use Storm\Contract\Tracker\StreamTracker;
use Storm\Contract\Tracker\TransactionalStreamTracker;
use Storm\Stream\Stream;
use Storm\Stream\StreamName;

readonly class EventChronicler implements EventableChronicler
{
    public function __construct(
        protected Chronicler|TransactionalChronicler $chronicler,
        protected StreamTracker|TransactionalStreamTracker $streamTracker
    ) {
        ProvideEvents::withEvent($this->chronicler, $this->streamTracker);
    }

    public function append(Stream $stream): void
    {
        $story = $this->streamTracker->newStory(self::APPEND_STREAM_EVENT);

        $story->deferred(static fn (): Stream => $stream);

        $this->streamTracker->disclose($story);

        // todo implement append exception
        if ($story->hasException()) {
            throw $story->exception();
        }
    }

    public function delete(StreamName $streamName): void
    {
        $story = $this->streamTracker->newStory(self::DELETE_STREAM_EVENT);

        $story->deferred(static fn (): StreamName => $streamName);

        $this->streamTracker->disclose($story);

        if ($story->hasStreamNotFound()) {
            throw $story->exception();
        }
    }

    public function retrieveAll(StreamName $streamName, AggregateIdentity $aggregateId, Direction $direction = Direction::FORWARD): Generator
    {
        $eventName = $direction === Direction::FORWARD ? self::ALL_STREAM_EVENT : self::ALL_BACKWARDS_STREAM_EVENT;

        $story = $this->streamTracker->newStory($eventName);

        $story->deferred(static fn (): array => [$streamName, $aggregateId, $direction]);

        $this->streamTracker->disclose($story);

        if ($story->hasStreamNotFound()) {
            throw $story->exception();
        }

        /** @var Generator $streamEvents */
        $streamEvents = $story->promise()->events();

        return $streamEvents;
    }

    public function retrieveFiltered(StreamName $streamName, QueryFilter $queryFilter): Generator
    {
        $story = $this->streamTracker->newStory(self::FILTERED_STREAM_EVENT);

        $story->deferred(static fn (): array => [$streamName, $queryFilter]);

        $this->streamTracker->disclose($story);

        if ($story->hasStreamNotFound()) {
            throw $story->exception();
        }

        /** @var Generator $streamEvents */
        $streamEvents = $story->promise()->events();

        return $streamEvents;
    }

    public function filterStreams(string ...$streams): array
    {
        $story = $this->streamTracker->newStory(self::FILTER_STREAM_EVENT);

        $story->deferred(static fn (): array => $streams);

        $this->streamTracker->disclose($story);

        return $story->promise();
    }

    public function filterCategories(string ...$categories): array
    {
        $story = $this->streamTracker->newStory(self::FILTER_CATEGORY_EVENT);

        $story->deferred(static fn (): array => $categories);

        $this->streamTracker->disclose($story);

        return $story->promise();
    }

    public function hasStream(StreamName $streamName): bool
    {
        $story = $this->streamTracker->newStory(self::HAS_STREAM_EVENT);

        $story->deferred(static fn (): StreamName => $streamName);

        $this->streamTracker->disclose($story);

        return $story->promise();
    }

    public function subscribe(string $eventName, callable $streamContext, int $priority = 0): Listener
    {
        return $this->streamTracker->listen(
            new StreamListener($eventName, $streamContext, $priority)
        );
    }

    public function unsubscribe(Listener ...$eventSubscribers): void
    {
        foreach ($eventSubscribers as $eventSubscriber) {
            $this->streamTracker->forget($eventSubscriber);
        }
    }

    public function innerChronicler(): Chronicler
    {
        return $this->chronicler;
    }
}
