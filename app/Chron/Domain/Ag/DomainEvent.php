<?php

declare(strict_types=1);

namespace App\Chron\Domain\Ag;

use stdClass;

class DomainEvent
{
    public function __construct(public stdClass $event)
    {
    }

    public function metadata(): stdClass
    {
        return $this->event->metadata;
    }

    public function content(): stdClass
    {
        return $this->event->content;
    }
}
