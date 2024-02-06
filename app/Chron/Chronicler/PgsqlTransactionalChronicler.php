<?php

declare(strict_types=1);

namespace App\Chron\Chronicler;

use App\Chron\Aggregate\Contract\AggregateIdentity;
use App\Chron\Chronicler\Contracts\QueryFilter;
use App\Chron\Chronicler\Contracts\StreamPersistence;
use App\Chron\Chronicler\Contracts\TransactionalChronicler;
use App\Chron\Chronicler\Exception\ConnectionConcurrencyFailure;
use App\Chron\Chronicler\Exception\ConnectionQueryFailure;
use App\Chron\EventPublisher\DirectEventPublisher;
use Generator;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Storm\Chronicler\Exceptions\StreamNotFound;
use Storm\Contract\Chronicler\EventStreamProvider;
use Storm\Stream\Stream;
use Storm\Stream\StreamName;

use function iterator_to_array;

final readonly class PgsqlTransactionalChronicler implements TransactionalChronicler
{
    use TransactionalStoreTrait;

    public function __construct(
        protected Connection $connection,
        protected EventStreamProvider $eventStreamProvider,
        protected StreamPersistence $streamPersistence,
        protected CursorConnectionLoader $streamEventLoader,
        //protected DirectEventPublisher $eventPublisher
    ) {
    }

    public function append(Stream $stream): void
    {
        $events = iterator_to_array($stream->events());

        $streamEvents = $this->streamPersistence->serialize($stream->name(), ...$events);

        if ($streamEvents === []) {
            return;
        }

        try {
            $this->writeQuery()->insert($streamEvents);

            //$this->eventPublisher->publish(...$events);
        } catch (QueryException $exception) {
            $this->handleException($exception, $stream->name());
        }
    }

    public function delete(StreamName $streamName): void
    {
        try {
            $deleted = $this->eventStreamProvider->deleteStream($streamName->name);

            if (! $deleted) {
                throw StreamNotFound::withStreamName($streamName);
            }
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '00000') {
                throw $exception;
            }
        }

        try {
            $this->connection->getSchemaBuilder()->drop($streamName->name);
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '00000') {
                throw $exception;
            }
        }
    }

    public function retrieveAll(StreamName $streamName, AggregateIdentity $aggregateId, Direction $direction = Direction::FORWARD): Generator
    {
        $query = $this->readQuery()
            ->where('id', $aggregateId->toString())
            ->orderBy('position', $direction->value);

        return $this->streamEventLoader->load($query, $streamName);
    }

    public function retrieveFiltered(StreamName $streamName, QueryFilter $queryFilter): Generator
    {
        $query = $this->readQuery();

        $queryFilter->apply()($query);

        return $this->streamEventLoader->load($query, $streamName);
    }

    public function filterStreams(string ...$streams): array
    {
        return $this->eventStreamProvider->filterByAscendantStreams($streams);
    }

    public function filterCategories(string ...$categories): array
    {
        return $this->eventStreamProvider->filterByAscendantCategories($categories);
    }

    public function hasStream(StreamName $streamName): bool
    {
        return $this->eventStreamProvider->hasRealStreamName($streamName->name);
    }

    private function handleException(QueryException $exception, StreamName $streamName): void
    {
        match ($exception->getCode()) {
            '42P01' => throw StreamNotFound::withStreamName($streamName),
            '23000', '23505' => throw new ConnectionConcurrencyFailure($exception->getMessage(), (int) $exception->getCode(), $exception),
            default => throw new ConnectionQueryFailure($exception->getMessage(), (int) $exception->getCode(), $exception)
        };
    }

    private function readQuery(): Builder
    {
        return $this->connection->table('stream_event');
    }

    private function writeQuery(): Builder
    {
        return $this->connection->table('stream_event')->useWritePdo();
    }
}
