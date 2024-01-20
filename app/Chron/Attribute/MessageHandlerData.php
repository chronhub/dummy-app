<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use App\Chron\Attribute\MessageHandler\AsCommandHandler;
use App\Chron\Attribute\MessageHandler\AsEventHandler;
use App\Chron\Attribute\MessageHandler\AsQueryHandler;
use App\Chron\Reporter\DomainType;
use ReflectionClass;

class MessageHandlerData
{
    public int $priority;

    public string $reporterId;

    public string|array|null $queue;

    public string $handles;

    public DomainType $type;

    public function __construct(
        public ReflectionClass $reflectionClass,
        protected AsCommandHandler|AsEventHandler|AsQueryHandler $instance,
        public string $handlerMethod,
    ) {
        $this->queue = $this->instance->fromQueue;
        $this->priority = $this->instance->priority ?? 0;
        $this->reporterId = $this->instance->reporter;
        $this->handles = $this->instance->handles;
        $this->type = $this->instance->type();
    }
}
