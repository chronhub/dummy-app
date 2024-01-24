<?php

declare(strict_types=1);

namespace App\Chron\Reporter\Subscribers;

use JsonSerializable;
use RuntimeException;

use function sprintf;

class QueueData implements JsonSerializable
{
    public function __construct(
        public int $priority,
        public string $name,
        public ?array $queue,
        public bool $dispatched,
        public bool $handled
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['priority'],
            $data['name'],
            $data['queue'],
            $data['dispatched'],
            $data['handled'],
        );
    }

    public static function newInstance(int $priority, string $name, ?array $queue): self
    {
        return new self(
            $priority,
            $name,
            $queue,
            false,
            false,
        );
    }

    public function markAsDispatched(): void
    {
        if ($this->dispatched) {
            throw new RuntimeException(sprintf('Queue %s already dispatched', $this->name));
        }

        $this->dispatched = true;
    }

    public function markAsHandled(): void
    {
        if ($this->handled) {
            throw new RuntimeException(sprintf('Queue %s already handled', $this->name));
        }

        $this->handled = true;
    }

    public function isCompleted(): bool
    {
        return $this->dispatched && $this->handled;
    }

    public function isNew(): bool
    {
        return ! $this->dispatched && ! $this->handled;
    }

    public function jsonSerialize(): array
    {
        return [
            'priority' => $this->priority,
            'name' => $this->name,
            'queue' => $this->queue,
            'handled' => $this->handled,
            'dispatched' => $this->dispatched,
        ];
    }
}
