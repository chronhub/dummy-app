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

    private ?array $queue;

    public function __construct(callable $handler, int $priority, ?array $queue)
    {
        $this->handler = $handler;
        $this->priority = $priority;
        $this->queue = $queue;
    }

    public function __invoke(mixed ...$arguments): mixed
    {
        return ($this->handler)(...$arguments);
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function queue(): ?array
    {
        return $this->queue;
    }

    public function handlerClass(): string
    {
        return $this->handler::class;
    }
}
