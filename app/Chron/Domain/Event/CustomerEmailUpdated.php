<?php

declare(strict_types=1);

namespace App\Chron\Domain\Event;

use Storm\Message\AbstractDomainEvent;

final class CustomerEmailUpdated extends AbstractDomainEvent
{
    public static function withCustomer(): self
    {
        return new self([]);
    }
}
