<?php

declare(strict_types=1);

namespace App\Chron\Package\Attribute\StreamSubscriber;

class StreamSubscriberHandler
{
    /**
     * @var callable
     */
    public $instance;

    public function __construct(
        public string $event,
        callable $instance,
        public int $priority,
    ) {
        $this->instance = $instance;
    }
}
