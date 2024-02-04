<?php

declare(strict_types=1);

namespace App\Chron\Aggregate;

use App\Chron\Aggregate\Contract\AggregateIdentity;
use App\Chron\Aggregate\Contract\AggregateRepository;
use App\Chron\Aggregate\Contract\AggregateRoot;
use App\Chron\Chronicler\Contracts\Chronicler;
use App\Chron\Chronicler\Contracts\QueryFilter;
use Generator;
use Storm\Chronicler\Exceptions\StreamNotFound;
use Storm\Contract\Message\DomainEvent;
use Storm\Contract\Message\EventHeader;
use Storm\Contract\Message\MessageDecorator;
use Storm\Message\Message;
use Storm\Stream\Stream;
use Storm\Stream\StreamName;

use function array_map;
use function count;
use function reset;

final readonly class GenericAggregateRepository implements AggregateRepository
{
    public function __construct(
        protected Chronicler $chronicler,
        protected StreamName $streamName,
        protected MessageDecorator $messageDecorator,
    ) {
    }

    public function retrieve(AggregateIdentity $aggregateId): ?AggregateRoot
    {
        return $this->reconstituteAggregate($aggregateId);
    }

    public function retrieveFiltered(AggregateIdentity $aggregateId, QueryFilter $queryFilter): ?AggregateRoot
    {
        return $this->reconstituteAggregate($aggregateId, $queryFilter);
    }

    public function retrieveHistory(AggregateIdentity $aggregateId, ?QueryFilter $queryFilter): Generator
    {
        if ($queryFilter instanceof QueryFilter) {
            return $this->chronicler->retrieveFiltered($this->streamName, $queryFilter);
        }

        return $this->chronicler->retrieveAll($this->streamName, $aggregateId);
    }

    public function store(AggregateRoot $aggregateRoot): void
    {
        $events = $this->releaseEvents($aggregateRoot);

        if ($events === []) {
            return;
        }

        $this->chronicler->append(new Stream($this->streamName, $events));
    }

    private function reconstituteAggregate(AggregateIdentity $aggregateId, ?QueryFilter $queryFilter = null): ?AggregateRoot
    {
        try {
            $history = $this->retrieveHistory($aggregateId, $queryFilter);

            if (! $history->valid()) {
                return null;
            }

            $firstEvent = $history->current();

            $aggregateType = $firstEvent->header(EventHeader::AGGREGATE_TYPE);

            return $aggregateType::reconstitute($aggregateId, $history);
        } catch (StreamNotFound) {
            return null;
        }
    }

    /**
     * @return array<DomainEvent>|array
     */
    private function releaseEvents(AggregateRoot $aggregate): array
    {
        $events = $aggregate->releaseEvents();

        if (! reset($events)) {
            return [];
        }

        $version = $aggregate->version() - count($events);

        return $this->decorateReleasedEvents($aggregate, $version, $events);
    }

    /**
     * @param  array<DomainEvent> $events
     * @param  positive-int       $version
     * @return array<DomainEvent>
     */
    private function decorateReleasedEvents(AggregateRoot $aggregate, int $version, array $events): array
    {
        $headers = [
            EventHeader::AGGREGATE_ID => $aggregate->identity()->toString(),
            EventHeader::AGGREGATE_ID_TYPE => $aggregate->identity()::class,
            EventHeader::AGGREGATE_TYPE => $aggregate::class,
        ];

        return array_map(function (DomainEvent $event) use ($headers, &$version) {
            return $this->messageDecorator->decorate(
                new Message($event, $headers + [EventHeader::AGGREGATE_VERSION => ++$version])
            )->event();
        }, $events);
    }
}
