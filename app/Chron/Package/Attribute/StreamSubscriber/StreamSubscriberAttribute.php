<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute\StreamSubscriber;

class StreamSubscriberAttribute
{
    public function __construct(
        public string $event,
        public string $subscriberClass,
        public array $chroniclers,
        public string $method,
        public int $priority,
        public bool $autowire,
        public array $references,
    ) {
    }
}
