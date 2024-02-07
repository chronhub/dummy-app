<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler;

use App\Chron\Package\Serializer\StreamEventSerializer;
use Generator;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Storm\Chronicler\Exceptions\NoStreamEventReturn;
use Storm\Chronicler\Exceptions\StreamNotFound;
use Storm\Contract\Message\DomainEvent;
use Storm\Serializer\Payload;
use Storm\Stream\StreamName;

class CursorConnectionLoader
{
    public function __construct(protected StreamEventSerializer $streamEventSerializer)
    {
    }

    public function load(Builder $builder, StreamName $streamName): Generator
    {
        $streamEvents = $builder->cursor();

        yield from $this->deserializeEvents($streamEvents, $streamName);

        return $streamEvents->count();
    }

    /**
     * @return Generator<DomainEvent>
     *
     * @throws StreamNotFound
     * @throws NoStreamEventReturn
     */
    protected function deserializeEvents(iterable $streamEvents, StreamName $streamName): Generator
    {
        try {
            $count = 0;

            foreach ($streamEvents as $streamEvent) {
                // fixMe metadata first in args
                $payload = new Payload(
                    $streamEvent->content,
                    $streamEvent->metadata,
                    $streamEvent->position,
                );

                yield $this->streamEventSerializer->deserializePayload($payload);

                $count++;
            }

            if ($count === 0) {
                throw NoStreamEventReturn::withStreamName($streamName);
            }

            return $count;
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '00000') {
                throw StreamNotFound::withStreamName($streamName);
            }
        }
    }
}
