<?php

declare(strict_types=1);

namespace App\Chron\Chronicler;

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

    public function createStream(string $streamName, ?string $streamTable, ?string $category = null): bool
    {
        $eventStream = new EventStream($streamName, $streamTable, $category);

        return $this->newQuery()->insert($eventStream->jsonSerialize());
    }

    public function deleteStream(string $streamName): bool
    {
        return $this->newQuery()->where('real_stream_name', $streamName)->delete() === 1;
    }

    public function filterByAscendantStreams(array $streamNames): array
    {
        return $this->newQuery()
            ->whereIn('real_stream_name', $streamNames)
            ->pluck('real_stream_name')
            ->toArray();
    }

    public function filterByAscendantCategories(array $categoryNames): array
    {
        return $this->newQuery()
            ->whereIn('category', $categoryNames)
            ->pluck('real_stream_name')
            ->toArray();
    }

    public function allWithoutInternal(): array
    {
        return $this->newQuery()
            ->whereRaw("real_stream_name NOT LIKE '$%'")
            ->pluck('real_stream_name')
            ->toArray();
    }

    public function hasRealStreamName(string $streamName): bool
    {
        return $this->newQuery()->where('real_stream_name', $streamName)->exists();
    }

    private function newQuery(): Builder
    {
        return $this->connection->table($this->tableName);
    }
}
