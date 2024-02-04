<?php

declare(strict_types=1);

namespace App\Chron\Chronicler;

use App\Chron\Chronicler\Contracts\StreamPersistence;
use App\Chron\Serializer\StreamEventSerializer;
use Storm\Contract\Clock\SystemClock;
use Storm\Contract\Message\DomainEvent;
use Storm\Contract\Message\EventHeader;
use Storm\Stream\StreamName;

final readonly class StandardStreamPersistence implements StreamPersistence
{
    public function __construct(
        protected StreamEventSerializer $streamEventSerializer,
        protected SystemClock $clock
    ) {
    }

    public function serialize(StreamName $streamName, DomainEvent ...$streamEvents): array
    {
        $events = [];

        foreach ($streamEvents as $streamEvent) {
            $events[] = $this->serializeEvent($streamEvent, $streamName);
        }

        return $events;
    }

    protected function serializeEvent(DomainEvent $event, StreamName $streamName): array
    {
        $payload = $this->streamEventSerializer->serializeEvent($event);

        return [
            'stream_name' => $streamName->name,
            'type' => $payload->headers[EventHeader::AGGREGATE_TYPE],
            'id' => $payload->headers[EventHeader::AGGREGATE_ID],
            'version' => $payload->headers[EventHeader::AGGREGATE_VERSION],
            'metadata' => $this->streamEventSerializer->encodePayload($payload->headers),
            'content' => $this->streamEventSerializer->encodePayload($payload->content),
            'created_at' => $this->clock->generate(),
        ];
    }
}
