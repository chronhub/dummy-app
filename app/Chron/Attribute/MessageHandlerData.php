<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

use ReflectionClass;

class MessageHandlerData
{
    public function __construct(
        public ReflectionClass $reflectionClass,
        public string $handlerMethod,
        public string $reporterId,
        public string|array|null $queue
    ) {
    }
}
