<?php

declare(strict_types=1);

namespace App\Chron\Model;

use function sprintf;

class InvalidDomainException extends DomainException
{
    public static function eventNotSupported(string $aggregateRoot, string $event): self
    {
        return new self(sprintf('Event %s not supported for aggregate %s', $event, $aggregateRoot));
    }
}
