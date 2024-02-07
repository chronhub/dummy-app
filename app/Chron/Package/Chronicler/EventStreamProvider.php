<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Storm\Contract\Chronicler\EventStreamProvider as Provider;

final readonly class EventStreamProvider implements Provider
{
    public function __construct(
        protected Connection $connection,
        protected string $tableName = 'event_stream'
    ) {
    }

    // todo change category to partition in contract
    public function createStream(string $streamName, ?string $streamTable, ?string $category = null): bool
    {
        $eventStream = new EventStream($streamName, $streamTable, $category);

        return $this->connect()->insert($eventStream->jsonSerialize());
    }

    public function deleteStream(string $streamName): bool
    {
        return $this->connect()->where('real_stream_name', $streamName)->delete() === 1;
    }

    // todo: change name to filterByStreams
    public function filterByAscendantStreams(array $streams): array
    {
        return $this->connect()
            ->whereIn('real_stream_name', $streams)
            ->pluck('real_stream_name')
            ->toArray();
    }

    // todo: change name to filterByCategories
    public function filterByAscendantCategories(array $categories): array
    {
        return $this->connect()
            ->whereIn('category', $categories)
            ->pluck('real_stream_name')
            ->toArray();
    }

    public function allWithoutInternal(): array
    {
        return $this->connect()
            ->whereRaw("real_stream_name NOT LIKE '$%'")
            ->pluck('real_stream_name')
            ->toArray();
    }

    public function hasRealStreamName(string $streamName): bool
    {
        return $this->connect()->where('real_stream_name', $streamName)->exists();
    }

    private function connect(): Builder
    {
        return $this->connection->table($this->tableName);
    }
}
