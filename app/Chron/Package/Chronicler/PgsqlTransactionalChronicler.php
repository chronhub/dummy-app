<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler;

use App\Chron\Package\Aggregate\Contract\AggregateIdentity;
use App\Chron\Package\Attribute\Chronicler\AsChronicler;
use App\Chron\Package\Chronicler\Contracts\QueryFilter;
use App\Chron\Package\Chronicler\Contracts\StreamPersistence;
use App\Chron\Package\Chronicler\Contracts\TransactionalChronicler;
use App\Chron\Package\Chronicler\Exception\ConnectionConcurrencyFailure;
use App\Chron\Package\Chronicler\Exception\ConnectionQueryFailure;
use Generator;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Storm\Chronicler\Exceptions\StreamNotFound;
use Storm\Contract\Chronicler\EventStreamProvider;
use Storm\Stream\Stream;
use Storm\Stream\StreamName;

#[AsChronicler(
    connection: 'pgsql',
    abstract: 'chronicler.event.transactional.standard.pgsql'
)]
final readonly class PgsqlTransactionalChronicler implements TransactionalChronicler
{
    use TransactionalStoreTrait;

    public function __construct(
        protected Connection $connection,
        protected EventStreamProvider $eventStreamProvider,
        protected StreamPersistence $streamPersistence,
        protected CursorConnectionLoader $streamEventLoader,
        protected string $masterTable = 'stream_event',
    ) {
        // todo assert event stream provider is a laravel connection
    }

    public function append(Stream $stream): void
    {
        $streamEvents = $this->streamPersistence->serialize($stream);

        if ($streamEvents === []) {
            return;
        }

        try {
            $this->connection->table($this->masterTable)->useWritePdo()->insert($streamEvents);
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
        $query = $this->connection->table($this->masterTable)
            ->where('id', $aggregateId->toString())
            ->orderBy('position', $direction->value);

        return $this->streamEventLoader->load($query, $streamName);
    }

    public function retrieveFiltered(StreamName $streamName, QueryFilter $queryFilter): Generator
    {
        $query = $this->connection->table($this->masterTable);

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
        // todo all exceptions must be caught by the rollback transaction
        match ($exception->getCode()) {
            '42P01' => throw StreamNotFound::withStreamName($streamName),
            '23000', '23505' => throw new ConnectionConcurrencyFailure($exception->getMessage(), (int) $exception->getCode(), $exception),
            default => throw new ConnectionQueryFailure($exception->getMessage(), (int) $exception->getCode(), $exception)
        };
    }

    protected function connection(): Connection
    {
        return $this->connection;
    }
}
