<?php

declare(strict_types=1);

namespace App\Chron\Attribute\MessageHandler;

class MessageHandler
{
    private string $name;

    /**
     * @var callable
     */
    private $handler;

    private int $priority;

    private ?array $queue;

    public function __construct(string $name, callable $handler, int $priority, ?array $queue)
    {
        $this->name = $name;
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

    public function name(): string
    {
        return $this->name;
    }
}
