<?php

declare(strict_types=1);

namespace App\Chron\Domain\Event;

use Storm\Message\AbstractDomainEvent;

final class CustomerRegistered extends AbstractDomainEvent
{
    public string $customerId;

    public string $name;

    public string $email;

    public static function withCustomer(
        string $customerId,
        string $name,
        string $email,
    ): self {
        $self = new self([]);
        $self->customerId = $customerId;
        $self->name = $name;
        $self->email = $email;

        return $self;
    }
}
