<?php

declare(strict_types=1);

namespace App\Chron\Attribute;

class MessageHandler
{
    /**
     * @var callable
     */
    private $handler;

    private int $priority;

    public function __construct(callable $handler, int $priority)
    {
        $this->handler = $handler;
        $this->priority = $priority;
    }

    public function __invoke(mixed ...$arguments): mixed
    {
        return ($this->handler)(...$arguments);
    }

    public function priority(): int
    {
        return $this->priority;
    }
}
