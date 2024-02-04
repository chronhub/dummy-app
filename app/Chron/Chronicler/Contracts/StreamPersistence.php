<?php

declare(strict_types=1);

namespace App\Chron\Chronicler\Contracts;

use Storm\Contract\Message\DomainEvent;
use Storm\Stream\StreamName;

interface StreamPersistence
{
    public function serialize(StreamName $streamName, DomainEvent ...$streamEvents): array;
}
