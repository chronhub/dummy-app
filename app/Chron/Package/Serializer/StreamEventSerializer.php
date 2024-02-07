<?php

declare(strict_types=1);

namespace App\Chron\Package\Serializer;

use Storm\Contract\Message\DomainEvent;
use Storm\Serializer\Payload;

interface StreamEventSerializer
{
    public function serializeEvent(DomainEvent $event): Payload;

    public function deserializePayload(Payload $payload): DomainEvent;

    public function encodePayload(array $payload): string;
}
